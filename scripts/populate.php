<?php
require_once '/var/www/html/vendor/autoload.php';

use App\Services\DbService;
use App\Services\UserService;
use App\Services\TransactionService;
use App\Models\User;
use App\Models\Transaction;

$dbService = new DbService();
$pdo = $dbService->getConnection();

$userService = new UserService($pdo);
$transactionService = new TransactionService($pdo);

// Truncate tables para reiniciar os testes
$userService->truncateUsers();
$transactionService->truncateTransactions();

// --- Criar usuários ---
$users = [
    ['Anakin Skywalker', '12345678901', 'anakin@example.com', 'common', 10.00],
    ['Luke Skywalker', '12345678902', 'luke@example.com', 'common', 5.00],
    ['Leia Organa', '12345678903', 'leia@example.com', 'merchant', 0.00],
    ['Han Solo', '12345678904', 'han@example.com', 'merchant', 0.00],
];

$createdUsers = [];
foreach ($users as [$name, $cpf, $email, $type, $balance]) {
    $createdUsers[$email] = $userService->createUser(new User(
        name: $name,
        cpfCnpj: $cpf,
        email: $email,
        password: '123456', // senha padrão
        type: $type,
        balance: $balance
    ));
}

echo "Usuários criados com sucesso!\n";

// --- Criar transações ---
$transactions = [
    [$createdUsers['anakin@example.com']->getId(), $createdUsers['leia@example.com']->getId(), 3.00],
    [$createdUsers['anakin@example.com']->getId(), $createdUsers['luke@example.com']->getId(), 2.00],
    [$createdUsers['luke@example.com']->getId(), $createdUsers['han@example.com']->getId(), 1.50],
    [$createdUsers['luke@example.com']->getId(), $createdUsers['leia@example.com']->getId(), 2.50],
    [$createdUsers['anakin@example.com']->getId(), $createdUsers['han@example.com']->getId(), 1.00],
];

foreach ($transactions as [$senderId, $receiverId, $amount]) {
    $transactionService->createTransferTransaction(new Transaction(
        sender_id: $senderId,
        receiver_id: $receiverId,
        amount: $amount,
        type: 'transfer'
    ));
}

echo "Transações criadas com sucesso!\n";

// Mostrar saldo final de cada usuário
foreach ($createdUsers as $email => $user) {
    $u = $userService->getUserById($user->getId());
    echo "{$u->getName()} ({$u->getTypeFormatted()}): R$ " . number_format($u->getBalance(), 2, ',', '.') . "\n";
}
