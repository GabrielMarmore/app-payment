<?php
if (!defined('APP_ENTRY_POINT')) {
    header("Location: /index.php?page=login");
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\DbService;
use App\Services\UserService;
use App\Services\TransactionService;

$dbService = new DbService();
$pdo = $dbService->getConnection();
$userService = new UserService($pdo);

$user = $userService->getUserById((int) $_SESSION['user_id']);

$transactionService = new TransactionService($pdo);
$transactions = $transactionService->getAllTransactionsByUserId($user->getId());
?>

<main id="dashboard" class="container d-flex flex-column justify-content-center align-items-center my-5">
    <div class="w-75 w-md-100">
        <h2 class="mb-4 text-center">Dashboard</h2>

        <div>
            <h3 class="text-center">
                Bem-vindo, <strong><?= htmlspecialchars($user->getName()) ?></strong>! <br>
            </h3>
            <table class="table table-bordered table-dark">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Tipo de conta</th>
                        <th>Saldo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="text-light">
                        <td><?= htmlspecialchars($user->getEmail()) ?></td>
                        <td><?= $user->getTypeFormatted() ?></td>
                        <td>R$ <?= number_format($user->getBalance(), 2, ',', '.') ?></td>
                        <td class="d-flex justify-content-center align-items-center">
                            <a href="/index.php?page=logout" class="btn btn-danger btn-sm ms-2">
                                Sair
                            </a>
                            <a href="/index.php?page=transaction" class="btn btn-primary btn-sm mx-2">
                                Realizar transação
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-center my-2">
        <h3>
            Lista de transações
        </h3>
        <?php if (count($transactions) === 0): ?>
            <p>Nenhuma transação encontrada.</p>
        <?php else: ?>
            <table class="table table-striped table-dark">
                <thead>
                    <tr>
                        <th>UUID</th>
                        <th>De</th>
                        <th>Para</th>
                        <th>Valor</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Atualizado em</th>
                        <th>Criado em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t->getId()) ?></td>
                            <td><?= htmlspecialchars($t->getSender()?->getName() ?? '-') ?></td>
                            <td><?= htmlspecialchars($t->getReceiver()?->getName() ?? '-') ?></td>
                            <td>R$ <?= number_format($t->getAmount(), 2, ',', '.') ?></td>
                            <td><?= $t->getTypeFormatted() ?></td>
                            <td><?= $t->getStatusFormatted() ?></td>
                            <td><?= $t->getUpdatedAt()?->format('d/m/Y H:i:s') ?? '-' ?></td>
                            <td><?= $t->getCreatedAt()?->format('d/m/Y H:i:s') ?? '-' ?></td>
                            <td>
                                <?php if ($t->getReceiver()?->getId() === $user->getId() && $t->getStatus() === 'pending'): ?>
                                    <div class="d-flex justify-content-center align-items-center">
                                        <button class="btn btn-danger btn-sm ms-2" onclick="showRevertModal('<?= $t->getId() ?>')">
                                            Estornar
                                        </button>

                                        <button class="btn btn-primary btn-sm mx-2"
                                            onclick="confirmTransaction('<?= $t->getId() ?>')">
                                            Confirmar
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

<div class="modal fade" id="confirmRevertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja estornar esta transação?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmRevertBtn">Estornar</button>
            </div>
        </div>
    </div>
</div>


<script>
    let transactionToRevert = null;

    function showRevertModal(id) {
        transactionToRevert = id;
        const modal = new bootstrap.Modal(document.getElementById('confirmRevertModal'));
        modal.show();
    }

    document.getElementById('confirmRevertBtn').addEventListener('click', async function () {
        if (!transactionToRevert) return;

        const modalEl = document.getElementById('confirmRevertModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();

        const formData = new FormData();
        formData.append('transaction_id', transactionToRevert);

        try {
            const response = await fetch('/process/revert_process.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            showToast(result.success, result.message);

            if (result.success) setTimeout(() => location.reload(), 1000);
        } catch (error) {
            console.error(error);
            showToast(false);
        } finally {
            transactionToRevert = null;
        }
    });

    async function confirmTransaction(id) {
        const formData = new FormData();
        formData.append('transaction_id', id);

        try {
            const response = await fetch('/process/confirm_process.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            showToast(result.success, result.message);

            if (result.success) setTimeout(() => location.reload(), 1000);
        } catch (error) {
            console.error(error);
            showToast(false);
        }
    }
</script>