<?php
if (!defined('APP_ENTRY_POINT')) {
    header("Location: /index.php?page=login");
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\UserService;
use App\Services\DbService;

$dbService = new DbService();
$pdo = $dbService->getConnection();
$userService = new UserService($pdo);

$user = $userService->getUserById((int) $_SESSION['user_id']);
?>

<main id="dashboard" class="container d-flex flex-column justify-content-center align-items-center my-5">
    <div class="w-75 w-md-100">
        <h2 class="mb-4 text-center">Dashboard</h2>

        <div class="text-center">
            <p>
                Bem-vindo, <strong><?= htmlspecialchars($user->getName()) ?></strong>! <br>
                Seu e-mail: <?= htmlspecialchars($user->getEmail()) ?><br>
                Tipo de conta: <?= $user->getTypeFormatted() ?><br>
                Saldo: R$ <?= number_format($user->getBalance(), 2, ',', '.') ?>
            </p>
            <a href="/index.php?page=logout" class="btn btn-danger">Sair</a>
        </div>
    </div>
</main>