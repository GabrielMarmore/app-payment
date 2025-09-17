<?php

namespace Tests;
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use App\Services\DbService;
use App\Services\UserService;
use App\Services\TransactionService;
use App\Models\User;
use App\Models\Transaction;

class TransactionTest extends TestCase
{
    private $userService;
    private $transactionService;
    private $sender;
    private $receiver;

    protected function setUp(): void
    {
        $dbService = new DbService();
        $pdo = $dbService->getConnection();

        $this->userService = new UserService($pdo);
        $this->transactionService = new TransactionService($pdo);

        $this->transactionService->truncateTransactions();
        $this->userService->truncateUsers();

        $this->sender = $this->userService->createUser(new User(
            name: "Han Solo",
            cpfCnpj: "11111111111",
            email: "han@example.com",
            password: "falcon123",
            type: "common",
            balance: 10.00
        ));

        $this->receiver = $this->userService->createUser(new User(
            name: "Chewbacca",
            cpfCnpj: "11111111000111",
            email: "chewie@example.com",
            password: "rrrawww",
            type: "merchant",
            balance: 1.00
        ));
    }

    public function testCreateTransferTransaction()
    {
        $transaction = new Transaction(
            sender_id: $this->sender->getId(),
            receiver_id: $this->receiver->getId(),
            amount: 5.50,
            type: "transfer"
        );

        $createdTransaction = $this->transactionService->createTransferTransaction($transaction);

        $this->assertInstanceOf(Transaction::class, $createdTransaction);
        $this->assertEquals("pending", $createdTransaction->getStatus());
        $this->assertEquals(5.50, $createdTransaction->getAmount());

        $updatedSender = $this->userService->getUserById($this->sender->getId());
        $updatedReceiver = $this->userService->getUserById($this->receiver->getId());

        $this->assertEquals(4.50, $updatedSender->getBalance());
        $this->assertEquals(6.50, $updatedReceiver->getBalance());
    }
    
    public function testTransferFailsIfInsufficientBalance()
    {
        $transaction = new Transaction(
            sender_id: $this->sender->getId(),
            receiver_id: $this->receiver->getId(),
            amount: 100.00,
            type: "transfer"
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Saldo insuficiente.");

        $this->transactionService->createTransferTransaction($transaction);
    }
        public function testTransferFailsMerchant()
    {
        $transaction = new Transaction(
            sender_id: $this->receiver->getId(),
            receiver_id: $this->sender->getId(),
            amount: 1.00,
            type: "transfer"
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Apenas usuários comuns podem enviar dinheiro.");

        $this->transactionService->createTransferTransaction($transaction);
    }

    public function testDeposit()
    {
        $transaction = new Transaction(
            sender_id: $this->sender->getId(),
            amount: 10.00,
            type: "deposit"
        );

        $createdTransaction = $this->transactionService->createSoloTransaction($transaction);

        $this->assertInstanceOf(Transaction::class, $createdTransaction);
        $this->assertEquals("completed", $createdTransaction->getStatus());
        $this->assertEquals(10.00, $createdTransaction->getAmount());

        $updatedSender = $this->userService->getUserById($this->sender->getId());
        $this->assertEquals(20.00, $updatedSender->getBalance());
    }
}