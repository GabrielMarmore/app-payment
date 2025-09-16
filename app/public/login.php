<?php
require_once __DIR__ . '/templates/header.php';
?>

<main id="login" class="container d-flex flex-column justify-content-center align-items-center my-5">
    <div class="w-75 w-md-100">
        <h2 class="mb-4 text-center">Login</h2>

        <form action="/login_process.php" method="post" class="needs-validation" novalidate>
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
            Não tem conta? <a href="/register.php" class="text-decoration-underline"
                style="color: var(--blue);">Cadastre-se</a>
        </p>
    </div>
</main>

<?php
require_once __DIR__ . '/templates/footer.php';
?>