<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../vendor/autoload.php';

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

    if (empty($_POST['transaction_id'])) {
        throw new Exception("ID da transação é obrigatório.");
    }

    $transactionId = $_POST['transaction_id'];

    $transactionService->revertTransaction($transactionId, $currentUser);

    echo json_encode([
        "success" => true,
        "message" => "Transação estornada com sucesso!"
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