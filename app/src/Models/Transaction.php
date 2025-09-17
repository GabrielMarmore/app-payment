<?php

namespace App\Models;

class Transaction
{
    private ?string $id; //uuid
    private int $sender_id;
    private ?int $receiver_id = null;
    private float $amount;
    private string $type;
    private string $status;
    private ?\DateTimeImmutable $createdAt = null;
    private ?\DateTimeImmutable $updatedAt = null;
    private ?User $sender = null;
    private ?User $receiver = null;

    public function __construct(
        int $sender_id,
        float $amount,
        string $type,
        string $status = 'pending',
        ?int $receiver_id = null,
        ?string $id = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->sender_id = $sender_id;
        $this->amount = $amount;
        $this->type = $type;
        $this->status = $status;
        $this->receiver_id = $receiver_id ?? null;
        $this->updatedAt = $updatedAt ? new \DateTimeImmutable($updatedAt) : null;
        $this->createdAt = $createdAt ? new \DateTimeImmutable($createdAt) : null;
    }

    // Getters
    public function getId(): ?string
    {
        return $this->id;
    }
    public function getSenderId(): int
    {
        return $this->sender_id;
    }
    public function getReceiverId(): ?int
    {
        return $this->receiver_id;
    }
    public function getAmount(): float
    {
        return $this->amount;
    }
    public function getStatus(): string
    {
        return $this->status;
    }
    public function getStatusFormatted(): string
    {
        return match ($this->status) {
            'pending' => 'Pendente',
            'completed' => 'Concluída',
            'reversed' => 'Revertida'
        };
    }
    public function getType(): string
    {
        return $this->type;
    }
    public function getTypeFormatted(): string
    {
        return match ($this->type) {
            'transfer' => 'Transferência',
            'deposit' => 'Deposito',
            'withdraw' => 'Saque'
        };
    }
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function getReceiver(): ?User
    {
        return $this->receiver;
    }

    // Setters
    public function setReceiver(?User $receiver): void
    {
        $this->receiver = $receiver;
    }
    public function setSender(User $sender): void
    {
        $this->sender = $sender;
    }
}
