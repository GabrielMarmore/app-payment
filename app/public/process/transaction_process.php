<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Transaction;
use App\Services\TransactionService;
use App\Services\UserService;
use App\Services\DbService;

header('Content-Type: application/json');

try {
    $dbService = new DbService();
    $pdo = $dbService->getConnection();
    $userService = new UserService($pdo);
    $transactionService = new TransactionService($pdo);

    $currentUser = $_SESSION['user_id'] ?? null;
    if (!$currentUser) {
        throw new Exception("Usuário não logado.");
    }

    $requiredFields = ['type', 'amount'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("O campo '{$field}' é obrigatório.");
        }
    }

    $validTypes = ['transfer', 'deposit', 'withdraw'];
    $type = $_POST['type'];
    if (!in_array($type, $validTypes)) {
        throw new Exception("O tipo de transação é inválido.");
    }

    $amount = (float) $_POST['amount'];
    if ($amount <= 0) {
        throw new Exception("O valor da transação deve ser maior que zero.");
    }

    $transaction = null;

    if ($type === 'transfer') {
        if (empty($_POST['receiver_id'])) {
            throw new Exception("Selecione um destinatário para a transferência.");
        }
        $receiverId = (int) $_POST['receiver_id'];
        $transaction = new Transaction(
            sender_id: $currentUser,
            receiver_id: $receiverId,
            amount: $amount,
            type: 'transfer'
        );
        $createdTransaction = $transactionService->createTransferTransaction($transaction);

    } else { // deposit or withdraw
        $transaction = new Transaction(
            sender_id: $currentUser,
            receiver_id: null,
            amount: $amount,
            type: $type
        );
        $createdTransaction = $transactionService->createSoloTransaction($transaction);
    }

    echo json_encode([
        "success" => true,
        "message" => "Transação realizada com sucesso!",
        "data" => [
            "id" => $createdTransaction->getId(),
            "type" => $createdTransaction->getTypeFormatted(),
            "status" => $createdTransaction->getStatusFormatted(),
            "amount" => $createdTransaction->getAmount()
        ]
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
    exit;
}
