<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Services\UserService;
use App\Services\DbService;

header('Content-Type: application/json');

try {
    $dbService = new DbService();
    $pdo = $dbService->getConnection();
    $userService = new UserService($pdo);

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        throw new Exception("E-mail e senha são obrigatórios.");
    }

    $user = $userService->authenticate($email, $password);
    
    $_SESSION['user_id'] = $user->getId();
    
    echo json_encode([
        "success" => true,
        "message" => "Login realizado com sucesso!",
        "data" => [
            "id" => $user->getId(),
            "email" => $user->getEmail(),
            "name" => $user->getName(),
            "type" => $user->getType()
        ]
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
    exit;
}
