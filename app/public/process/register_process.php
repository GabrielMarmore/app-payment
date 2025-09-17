<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\User;
use App\Services\UserService;
use App\Services\DbService;

header('Content-Type: application/json');

try {
    $dbService = new DbService();
    $pdo = $dbService->getConnection();
    $userService = new UserService($pdo);
    
    //Balanche may be empty?
    $requiredFields = ['name', 'cpf_cnpj', 'email', 'password', 'type']; //,'balance'

    foreach ($requiredFields as $field) {
        if (empty($field)) throw new Exception("O campo '{$field}' é obrigatório.");
    }

    $validTypes = ['common', 'merchant'];
    if (!in_array($_POST['type'], $validTypes)) {
        throw new Exception("O tipo de usuário é inválido.");
    }


    $user = new User(
        name: trim($_POST['name']),
        cpfCnpj: trim($_POST['cpf_cnpj']),
        email: trim($_POST['email']),
        password: $_POST['password'],
        type: $_POST['type'],
        balance: (float) $_POST['balance']
    );
    $createdUser = $userService->createUser($user);

    echo json_encode([
        "success" => true,
        "message" => "Usuário criado com sucesso!",
        "data" => [
            "id" => $createdUser->getId(),
            "email" => $createdUser->getEmail()
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