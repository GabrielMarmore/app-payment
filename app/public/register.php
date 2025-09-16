<?php
if (!defined('APP_ENTRY_POINT')) {
    header("Location: /index.php?page=login");
    exit();
}
?>

<main id="register" class="container d-flex flex-column justify-content-center align-items-center my-5">
    <div class="w-75 w-md-100">
        <h2 class="mb-4 text-center">Cadastro</h2>

        <form class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="name" class="form-label">Nome completo</label>
                <input type="text" id="name" name="name" class="form-control" required placeholder="Digite seu nome">
                <div class="invalid-feedback">
                    Por favor, insira seu nome.
                </div>
            </div>

            <div class="mb-3">
                <label for="cpf_cnpj" class="form-label">CPF ou CNPJ</label>
                <input type="text" id="cpf_cnpj" name="cpf_cnpj" class="form-control" required
                    placeholder="Digite seu CPF ou CNPJ">
                <div class="invalid-feedback">
                    Por favor, insira um CPF ou CNPJ válido.
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" id="email" name="email" class="form-control" required
                    placeholder="Digite seu e-mail">
                <div class="invalid-feedback">
                    Por favor, insira um e-mail válido.
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input type="password" id="password" name="password" class="form-control" required
                    placeholder="Digite sua senha">
                <div class="invalid-feedback">
                    Por favor, insira uma senha.
                </div>
            </div>

            <div class="mb-3">
                <label for="type" class="form-label">Tipo de usuário</label>
                <select id="type" name="type" class="form-select" required>
                    <option value="">Selecione...</option>
                    <option value="common">Comum</option>
                    <option value="merchant">Lojista</option>
                </select>
                <div class="invalid-feedback">
                    Selecione o tipo de usuário.
                </div>
            </div>

            <div class="mb-3">
                <label for="balance" class="form-label">Saldo inicial</label>
                <input type="number" step="0.01" min="0" id="balance" name="balance" class="form-control"
                    placeholder="Saldo inicial">
                <!--
                <div class="invalid-feedback">
                    Insira um valor de saldo inicial.
                </div>
                -->
            </div>

            <div class="d-flex justify-content-center">
                <button type="submit" class="btn btn-primary w-50">Cadastrar</button>
            </div>
        </form>

        <p class="mt-3 text-center">
            Já tem conta? <a href="/index.php?page=login" class="text-decoration-underline" style="color: var(--blue);">Faça
                login</a>
        </p>
    </div>
</main>

<script>
    document.querySelector("#register form").addEventListener("submit", async function (e) {
        e.preventDefault();
        const form = this;
        let firstInvalid = null;

        // Reset
        form.querySelectorAll(".form-control, .form-select").forEach(input => input.classList.remove("is-invalid"));

        form.querySelectorAll("[required]").forEach(input => {
            if (!input.value.trim()) {
                input.classList.add("is-invalid"); // Boostrap handles this
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
            const response = await fetch("/process/register_process.php", {
                method: "POST",
                body: formData
            });

            const result = await response.json();

            showToast(result.success, result.message);
            if (result.success) setTimeout(() => window.location.href = "/index.php?page=login", 1000);
        } catch (error) {
            console.error(error);
            showToast(false);
        }
    });
</script>