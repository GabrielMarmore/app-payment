# Sistema de pagamento simplificado

Desafio técnico em **PHP puro**, utilizando **Docker** e **PostgreSQL**.  
O sistema permite transferências de dinheiro entre transferências de dinheiro entre **usuários comuns** e **lojistas**, com autenticação via login em PHP (sessions) e regras de negócio específicas. Também simula serviços externos de autorização e notificação para operações financeiras.

## Pré-requisitos
- Linux: Docker e Docker Compose 
- Windows: Docker Desktop

## Instalação e execução
- Clonar projeto:
```
git clone https://github.com/GabrielMarmore/app-payment.git
cd app-payment
```
- Subir os containers:
```
docker compose up --build -d
```
- Acessar:
```
http://localhost:8080
```

## Serviços
- App  
Container: app  
Porta exposta: 8080  
Rodando PHP 8.2 com Apache.
Acesso pelo navegador: http://localhost:8080  

- Mocks
Container: mocks  
Porta exposta: 9001  
Rodando servidor PHP embutido em /app  
Endpoints de teste:  
  - Authorizer: http://localhost:9001/authorizer.php  
  - Notification: http://localhost:9001/notification.php  
Acesso entre containers: usar http://mocks:9001 

- Banco de dados
Banco em PostgreSQL, mais elegante para lidar com transações por suportar o tipo numeric.
Configuração:  
  - Host: db  
  - Porta: 5432  
  - Banco: payments  
  - Usuário: user  
  - Senha: password  
Do host local, pode ser acessado em localhost:5432  


## Migrations
As migrations são aplicadas automaticamente quando o container da aplicação sobe.
Caso queira rodar manualmente, use:
```
docker compose exec app php /var/www/scripts/migrate.php
```

## Testes
Rodam com PHPUnit, já incluido no projeto.
- Testar banco de dados:
```
docker compose exec app bash
./vendor/bin/phpunit tests/DbTest.php
```
- Testar usuários:
Atenção: Antes do teste a tabela de usuarios é truncada.
```
docker compose exec app bash
./vendor/bin/phpunit tests/UserTest.php
```
- Testar transações:
Atenção: Antes do teste a tabela de usuarios e transações é truncada.
```
docker compose exec app bash
./vendor/bin/phpunit tests/TransactionTest.php
```

## Estrutura do projeto
O sistema segue uma arquitetura **monolítica** em PHP, com separação inspirada no **padrão MVC**:
- `/app` → código PHP principal, incluindo:
  - `Models` → definição das entidades do sistema (ex.: User, Transaction)  
  - `Services` → regras de negócio e manipulação de dados (ex.: TransactionService, UserService)  
  - `public` → páginas, templates e assets (CSS/JS)  
  - `process` → scripts para processamento de formulários e requisições AJAX  
- `/mocks` → serviços simulados externos (authorizer e notification)  
- `/scripts` → scripts auxiliares, como migrações (`migrate.php`) e populadores (`populate.php`)  
- `/migrations` → scripts SQL para criação e atualização de tabelas  
- `/tests` → testes unitários com PHPUnit  

## Fluxo de uso
1. Acesse http://localhost:8080  
2. Crie um usuário (comum ou lojista) e faça login.  
3. Realize operações: depósito, saque, transferência

## Boas práticas e padrões adotados
- **Organização simples em camadas**: Models, Services, Scripts, Mocks separados e arquivos públicos no diretório public, para manter o projeto claro e fácil de entender.
- **PSR-4 e PSR-12**: namespaces via Composer e padronização de estilo.
- **Princípios SOLID**:
  - *Single Responsibility*: classes focadas em uma única responsabilidade.
  - *Dependency Injection*: dependências como `PDO` injetadas no construtor.
- **Transações no banco**: uso de `beginTransaction`, `commit` e `rollBack` para consistência.
- **Validação de regras de negócio**: checagem de saldo, tipo de usuário (comum/lojista), autorização externa e notificações.
- **Tratamento de erros**: exceções lançadas em situações inválidas.
- **Segurança**:
  - Senhas com `password_hash` / `password_verify`.
  - Autenticação de login com sessões PHP.
- **Extensibilidade**: serviços externos simulados com *Mocks*, facilmente substituíveis por APIs reais.

## Comandos úteis

- Subir containers em background:  
```
docker compose up -d
```
- Subir containers com rebuild:  
```
docker compose up --build -d
```
- Parar containers:  
```
docker compose down
```
- Ver logs do container da aplicação:  
```
docker compose logs -f app
```
- Acessar o container da aplicação via shell:  
```
docker compose exec app bash
```
- Rodar PHPUnit dentro do container:
Adicionar ```-debug``` para mais detalhes  
```
docker compose exec app ./vendor/bin/phpunit tests/
```
- Rodar migrations manualmente:  
```
docker compose exec app php /var/www/scripts/migrate.php
```

- Popular banco de dados com usuários e transações de teste:
```
docker compose exec app php /var/www/scripts/populate.php
```

## Tecnologias
- **PHP 8.2 + Apache**
- **PostgreSQL 15**
- **Composer**
- **Boostrap 5**
- **Mocks** para simular:
  - Serviço externo de autorização
  - Envio de notificações (email/SMS)P
