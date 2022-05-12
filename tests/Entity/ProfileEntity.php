<?php declare(strict_types=1);

namespace Lightning\Test\Entity;

use Lightning\Entity\AbstractEntity;

class ProfileEntity extends AbstractEntity
{
    private int $id;
    private string $name;
    private int $user_id;
    private string $created_at;
    private string $updated_at;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getName(): ?string
    {
        return $this->name ?? null;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at ?? null;
    }

    public function setCreatedAt(string $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updated_at ?? null;
    }

    public function setUpdatedAt(string $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id ?? null;
    }

    public function setUserId(int $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }
}
