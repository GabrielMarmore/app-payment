<?php require_once __DIR__ . '/templates/header.php'; ?>
<main id="register" class="container d-flex flex-column justify-content-center align-items-center my-5">
    <div class="w-75 w-md-100">
        <h2 class="mb-4 text-center">Cadastro</h2>

        <form action="/register_process.php" method="post" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="name" class="form-label">Nome completo</label>
                <input type="text" id="name" name="name" class="form-control" required placeholder="Digite seu nome">
                <div class="invalid-feedback">
                    Por favor, insira seu nome.
                </div>
            </div>

            <div class="mb-3">
                <label for="cpf_cnpj" class="form-label">CPF ou CNPJ</label>
                <input type="text" id="cpf_cnpj" name="cpf_cnpj" class="form-control" required placeholder="Digite seu CPF ou CNPJ">
                <div class="invalid-feedback">
                    Por favor, insira um CPF ou CNPJ válido.
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" id="email" name="email" class="form-control" required placeholder="Digite seu e-mail">
                <div class="invalid-feedback">
                    Por favor, insira um e-mail válido.
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="Digite sua senha">
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
                <input type="number" step="0.01" min="0" id="balance" name="balance" class="form-control" required placeholder="Saldo inicial">
                <div class="invalid-feedback">
                    Insira um valor de saldo inicial.
                </div>
            </div>

            <div class="d-flex justify-content-center">
                <button type="submit" class="btn btn-primary w-50">Cadastrar</button>
            </div>
        </form>

        <p class="mt-3 text-center">
            Já tem conta? <a href="/login.php" class="text-decoration-underline" style="color: var(--blue);">Faça login</a>
        </p>
    </div>
</main>

<? require_once __DIR__ . '/templates/footer.php'; ?>