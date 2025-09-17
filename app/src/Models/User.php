<?php

namespace App\Models;

class User
{
    private ?int $id = null;
    private string $name;
    private string $cpfCnpj;
    private string $email;
    private string $password;
    private string $type;
    private float $balance;
    private ?\DateTimeImmutable $createdAt = null;
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(
        string $name,
        string $cpfCnpj,
        string $email,
        string $password,
        string $type,
        float $balance = 0.0,
        ?int $id = null,
        ?string $updatedAt = null,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->cpfCnpj = $cpfCnpj;
        $this->email = $email;
        $this->password = $password;
        $this->type = $type;
        $this->balance = $balance;
        $this->updatedAt = $updatedAt ? new \DateTimeImmutable($updatedAt) : null;
        $this->createdAt = $createdAt ? new \DateTimeImmutable($createdAt) : null;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCpfCnpj(): string
    {
        return $this->cpfCnpj;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTypeFormatted(): string
    {
        return match ($this->type) {
            'common' => 'Comum',
            'merchant' => 'Lojista'
        };
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'cpf_cnpj' => $this->cpfCnpj,
            'email' => $this->email,
            'type' => $this->type,
            'balance' => $this->balance,
            'updated_at' => $this->updatedAt,
            'created_at' => $this->createdAt,
        ];
    }
}
