<?php
if (!defined('APP_ENTRY_POINT')) {
    header("Location: /index.php?page=login");
    exit();
}
?>

<main id="login" class="container d-flex flex-column justify-content-center align-items-center my-5">
    <div class="w-75 w-md-100">
        <h2 class="mb-4 text-center">Login</h2>

        <form class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" id="email" name="email" class="form-control" required
                    placeholder="Digite seu email">
                <div class="invalid-feedback">
                    Por favor, insira um e-mail válido.
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input type="password" id="password" name="password" class="form-control" required
                    placeholder="Digite sua senha">
                <div class="invalid-feedback">
                    Por favor, insira sua senha.
                </div>
            </div>

            <div class="d-flex justify-content-center">
                <button type="submit" class="btn btn-primary w-50">Entrar</button>
            </div>
        </form>

        <p class="mt-3 text-center">
            Não tem conta? <a href="/index.php?page=register" class="text-decoration-underline"
                style="color: var(--blue);">Cadastre-se</a>
        </p>
    </div>
</main>

<script>
    document.querySelector("#login form").addEventListener("submit", async function (e) {
        e.preventDefault();

        const form = this;
        let firstInvalid = null;

        // Reset
        form.querySelectorAll(".form-control").forEach(input => input.classList.remove("is-invalid"));

        form.querySelectorAll("[required]").forEach(input => {
            if (!input.value.trim()) {
                input.classList.add("is-invalid");  // Boostrap handles this
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
            const response = await fetch("/process/login_process.php", {
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