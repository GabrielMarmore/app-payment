<?php

namespace App\Services;

use App\Models\User;
use PDO;
use PDOException;

class UserService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function checkCpfCnpj(string $cpfCnpj): void
    {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE cpf_cnpj = :cpf_cnpj");
        $stmt->execute(['cpf_cnpj' => $cpfCnpj]);
        if ($stmt->fetch()) {
            throw new \Exception("CPF/CNPJ já cadastrado.");
        }
    }

    private function checkEmail(string $email): void
    {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            throw new \Exception("E-mail já cadastrado.");
        }
    }

    public function createUser(User $user): User
    {
        $this->checkCpfCnpj($user->getCpfCnpj());
        $this->checkEmail($user->getEmail());

        $stmt = $this->pdo->prepare("
            INSERT INTO users (name, cpf_cnpj, email, password, type, balance)
            VALUES (:name, :cpf_cnpj, :email, :password, :type, :balance)
            RETURNING id
        ");

        $stmt->execute([
            'name' => $user->getName(),
            'cpf_cnpj' => $user->getCpfCnpj(),
            'email' => $user->getEmail(),
            'password' => password_hash($user->getPassword(), PASSWORD_DEFAULT),
            'type' => $user->getType(),
            'balance' => $user->getBalance()
        ]);

        $id = $stmt->fetchColumn();
        return $this->getUserById($id);
    }
    public function getUserById(int $id): User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw new \Exception("Usuário não encontrado.");
        }

        return new User(
            $data['name'],
            $data['cpf_cnpj'],
            $data['email'],
            '***',
            $data['type'],
            (float) $data['balance'],
            (int) $data['id'],
            $data['created_at']
        );
    }
    public function authenticate(string $email, string $password): User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw new \Exception("E-mail não encontrado.");
        }

        if (!password_verify($password, $data['password'])) {
            throw new \Exception("Senha incorreta.");
        }

        return new User(
            $data['name'],
            $data['cpf_cnpj'],
            $data['email'],
            '***',
            $data['type'],
            (float) $data['balance'],
            (int) $data['id'],
            $data['created_at']
        );
    }
    public function truncateUsers(): void
    {
        $this->pdo->exec("TRUNCATE TABLE users CASCADE");
    }
}
