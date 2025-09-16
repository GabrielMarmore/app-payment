# Sistema de pagamento simplificado

Desafio técnico em **PHP puro**, utilizando **Docker** e **PostgreSQL**.  
O sistema permitirá transferências de dinheiro entre **usuários comuns** e **lojistas**, com regras de negócio específicas.

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

## Migrations
As migrations são aplicadas automaticamente quando o container da aplicação sobe.
Caso queira rodar manualmente, use:
```
docker compose exec app php /var/www/scripts/migrate.php
```

## Testes
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


## Tecnologias
- **PHP 8.2 + Apache**
- **PostgreSQL 15**
- **Composer**
- **Boostrap 5**
- **Mocks** para simular:
  - Serviço externo de autorização
  - Envio de notificações (email/SMS)
