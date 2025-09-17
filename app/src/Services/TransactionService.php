<?php

namespace App\Services;

use App\Models\Transaction;
use PDO;

class TransactionService
{
    private PDO $pdo;
    private UserService $userService;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->userService = new UserService($pdo);
    }

    public function createTransferTransaction(Transaction $transaction): Transaction
    {
        $this->pdo->beginTransaction();

        try {
            $sender = $this->userService->getUserById($transaction->getSenderId());
            $receiver = $this->userService->getUserById($transaction->getReceiverId());

            if ($sender->getType() !== 'common') {
                throw new \Exception("Apenas usuários comuns podem enviar dinheiro.");
            }

            if ($sender->getBalance() < $transaction->getAmount()) {
                throw new \Exception("Saldo insuficiente.");
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO transactions (sender_id, receiver_id, amount, type, status)
                VALUES (:sender_id, :receiver_id, :amount, :type, :status)
                RETURNING id
            ");

            $stmt->execute([
                'sender_id' => $transaction->getSenderId(),
                'receiver_id' => $transaction->getReceiverId(),
                'amount' => $transaction->getAmount(),
                'type' => 'transfer',
                'status' => 'pending' //reciver may accecpt or reverse
            ]);

            $uuid = $stmt->fetchColumn();

            $updateSender = $this->pdo->prepare("UPDATE users SET balance = balance - :amount WHERE id = :id");
            $updateReceiver = $this->pdo->prepare("UPDATE users SET balance = balance + :amount WHERE id = :id");

            $updateSender->execute([
                'amount' => $transaction->getAmount(),
                'id' => $sender->getId()
            ]);

            $updateReceiver->execute([
                'amount' => $transaction->getAmount(),
                'id' => $receiver->getId()
            ]);

            $authResponse = file_get_contents('http://mocks:9001/authorizer.php');
            $authData = json_decode($authResponse, true);

            if (!$authData['approved']) {
                throw new \Exception("Transferência não autorizada pelo serviço externo.");
            }

            $this->pdo->commit();

            $notificationPayload = [
                'sender_id' => $sender->getId(),
                'receiver_id' => $receiver->getId(),
                'amount' => $transaction->getAmount(),
                'type' => 'transfer'
            ];

            $notifcationResponse = file_get_contents('http://mocks:9001/notification.php', false, stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-type: application/json\r\n",
                    'content' => json_encode($notificationPayload)
                ]
            ]));

            //var_dump($notifcationResponse); //

            return $this->getTransactionById($uuid);
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    public function createSoloTransaction(Transaction $transaction): Transaction
    {
        $this->pdo->beginTransaction();

        try {
            $sender = $this->userService->getUserById($transaction->getSenderId());

            if ($transaction->getType() === 'withdraw' && $sender->getBalance() < $transaction->getAmount()) {
                throw new \Exception("Saldo insuficiente.");
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO transactions (sender_id, receiver_id, amount, type, status)
                VALUES (:sender_id, :receiver_id, :amount, :type, :status)
                RETURNING id
            ");

            $stmt->execute([
                'sender_id' => $transaction->getSenderId(),
                'receiver_id' => null,
                'amount' => $transaction->getAmount(),
                'type' => $transaction->getType(),
                'status' => 'completed'
            ]);

            $uuid = $stmt->fetchColumn();
            $updateSender = $this->pdo->prepare(
                "UPDATE users SET balance = balance "
                . ($transaction->getType() === 'withdraw' ? '-' : '+') .
                " :amount WHERE id = :id"
            );

            $updateSender->execute([
                'amount' => $transaction->getAmount(),
                'id' => $sender->getId()
            ]);

            $this->pdo->commit();

            return $this->getTransactionById($uuid);
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getTransactionById(string $uuid): Transaction
    {
        $stmt = $this->pdo->prepare("SELECT * FROM transactions WHERE id = :id");
        $stmt->execute(['id' => $uuid]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw new \Exception("Transação não encontrada.");
        }

        $transaction = new Transaction(
            sender_id: (int) $data['sender_id'],
            receiver_id: (int) $data['receiver_id'],
            amount: (float) $data['amount'],
            type: $data['type'],
            status: $data['status'],
            id: $data['id'],
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null
        );

        $transaction->setSender($this->userService->getUserById($data['sender_id']));
        $transaction->setReceiver(
            $data['receiver_id'] ? $this->userService->getUserById($data['receiver_id']) : null
        );

        return $transaction;
    }

    public function getAllTransactionsByUserId(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM transactions WHERE sender_id = :id OR receiver_id = :id');
        $stmt->execute(['id' => $id]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function (array $data): Transaction {
            $transaction = new Transaction(
                sender_id: (int) $data['sender_id'],
                receiver_id: $data['receiver_id'] !== null ? (int) $data['receiver_id'] : null,
                amount: (float) $data['amount'],
                type: $data['type'],
                status: $data['status'],
                id: $data['id'],
                createdAt: $data['created_at'] ?? null,
                updatedAt: $data['updated_at'] ?? null
            );

            $transaction->setSender($this->userService->getUserById($data['sender_id']));
            $transaction->setReceiver(
                $data['receiver_id'] !== null ? $this->userService->getUserById($data['receiver_id']) : null
            );

            return $transaction;
        }, $transactions);
    }

    public function revertTransaction(string $transactionId, int $currentUserId): void
    {
        $this->pdo->beginTransaction();
        try {
            $transaction = $this->getTransactionById($transactionId);

            if ($transaction->getStatus() !== 'pending') {
                throw new \Exception("Transação não pode ser revertida.");
            }

            $sender = $transaction->getSender();
            $receiver = $transaction->getReceiver();

            if ($currentUserId !== $receiver->getId()) {
                throw new \Exception("Apenas o destinatário pode reverter a transação.");
            }

            $updateSender = $this->pdo->prepare(
                "UPDATE users SET balance = balance + :amount WHERE id = :id"
            );

            $updateSender->execute([
                'amount' => $transaction->getAmount(),
                'id' => $sender->getId()
            ]);

            $updateReceiver = $this->pdo->prepare(
                "UPDATE users SET balance = balance - :amount WHERE id = :id"
            );
            $updateReceiver->execute([
                'amount' => $transaction->getAmount(),
                'id' => $receiver->getId()
            ]);

            $updateTransaction = $this->pdo->prepare(
                "UPDATE transactions SET status = 'reversed' WHERE id = :id"
            );

            $updateTransaction->execute([
                'id' => $transaction->getId()
            ]);

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function confirmTransaction(string $transactionId, int $currentUserId): void
    {
        $this->pdo->beginTransaction();
        try {
            $transaction = $this->getTransactionById($transactionId);

            if ($transaction->getStatus() !== 'pending') {
                throw new \Exception("Transação não pode ser confirmada.");
            }

            $receiver = $transaction->getReceiver();
            if ($currentUserId !== $receiver->getId()) {
                throw new \Exception("Apenas o destinatário pode confirmar esta transação.");
            }

            $update = $this->pdo->prepare("UPDATE transactions SET status = 'completed' WHERE id = :id");
            $update->execute(['id' => $transaction->getId()]);

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }


    public function truncateTransactions(): void
    {
        $this->pdo->exec("TRUNCATE TABLE transactions");
    }
}
