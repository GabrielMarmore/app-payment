<?php
if (!defined('APP_ENTRY_POINT')) {
    header("Location: /index.php?page=login");
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\DbService;
use App\Services\UserService;

$dbService = new DbService();
$pdo = $dbService->getConnection();
$userService = new UserService($pdo);

$users = $userService->getAllUsers();
$filtred_users = array_filter($users, fn($u) => $u['id'] !== (int) $_SESSION['user_id']);

$currentUser = $userService->getUserById($_SESSION['user_id']);
?>

<main id="transaction" class="container d-flex flex-column justify-content-center align-items-center my-5">
    <div class="w-75 w-md-100">
        <h2 class="mb-4 text-center">Nova Transação</h2>

        <form class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="type" class="form-label">Tipo de transação</label>
                <select id="type" name="type" class="form-select" required>
                    <option value="">Selecione...</option>
                    <?php if ($currentUser->getType() === 'common'): ?>
                        <option value="transfer">Transferência</option>
                    <?php endif; ?>
                    <option value="deposit">Depósito</option>
                    <option value="withdraw">Saque</option>
                </select>
                <div class="invalid-feedback">
                    Selecione o tipo de transação.
                </div>
            </div>

            <div class="mb-3 d-none" id="receiver-container">
                <label for="receiver_id" class="form-label">Destinatário</label>
                <select id="receiver_id" name="receiver_id" class="form-select">
                    <option value="">Selecione...</option>
                    <?php foreach ($filtred_users as $user): ?>
                        <option value="<?= $user['id'] ?>">
                            <?= htmlspecialchars($user['name'] . '-' . $user['email']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">
                    Selecione um destinatário válido.
                </div>
            </div>

            <div class="mb-3">
                <label for="amount" class="form-label">Valor</label>
                <input type="number" step="0.01" min="0.01" id="amount" name="amount" class="form-control" required
                    placeholder="Digite o valor">
                <div class="invalid-feedback">
                    Insira um valor válido.
                </div>
            </div>

            <div class="d-flex justify-content-center">
                <a href="/index.php?page=dashboard" class="btn btn-secondary w-50 ms-2">Voltar para dashboard</a>
                <button type="submit" class="btn btn-primary w-50 mx-2">Efetuar</button>
            </div>
        </form>
    </div>
</main>

<script>
    const typeSelect = document.querySelector("#type");
    const receiverContainer = document.querySelector("#receiver-container");

    typeSelect.addEventListener("change", () => {
        if (typeSelect.value === "transfer") {
            receiverContainer.classList.remove("d-none");
            receiverContainer.querySelector("select").setAttribute("required", "required");
        } else {
            receiverContainer.classList.add("d-none");
            receiverContainer.querySelector("select").removeAttribute("required");
        }
    });

    document.querySelector("#transaction form").addEventListener("submit", async function (e) {
        e.preventDefault();
        const form = this;
        let firstInvalid = null;

        // Reset
        form.querySelectorAll(".form-control, .form-select").forEach(input => input.classList.remove("is-invalid"));

        form.querySelectorAll("[required]").forEach(input => {
            if (!input.value.trim()) {
                input.classList.add("is-invalid");
                if (!firstInvalid) firstInvalid = input;
            }
        });

        if (firstInvalid) {
            firstInvalid.focus();
            showToast(false, "Preencha todos os campos obrigatórios!");
            return;
        }

        const formData = new FormData(form);

        try {
            const response = await fetch("/process/transaction_process.php", {
                method: "POST",
                body: formData
            });

            const result = await response.json();
            showToast(result.success, result.message);

            if (result.success) setTimeout(() => window.location.href = "/index.php?page=dashboard", 1000);
        } catch (error) {
            console.error(error);
            showToast(false);
        }
    });
</script>