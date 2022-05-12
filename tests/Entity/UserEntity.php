<?php declare(strict_types=1);

namespace Lightning\Test\Entity;

use Lightning\Entity\AbstractEntity;

class UserEntity extends AbstractEntity
{
    private int $id;
    private string $name;
    private ?ProfileEntity $profile;
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

    public function getProfile(): ?ProfileEntity
    {
        return $this->profile ?? null;
    }

    /**
     * I am allowing null value her so i can test conditions
     */
    public function setProfile(?ProfileEntity $profile): static
    {
        $this->profile = $profile;

        return $this;
    }
}
