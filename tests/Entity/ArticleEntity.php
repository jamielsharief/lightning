<?php declare(strict_types=1);

namespace Lightning\Test\Entity;

use Lightning\Entity\AbstractEntity;

class ArticleEntity extends AbstractEntity
{
    private int $id;
    private string $title;
    private string $body;
    private int $author_id;
    private ?AuthorEntity $author;
    private string $created_at;
    private string $updated_at;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getTitle(): ?string
    {
        return $this->title ?? null;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body ?? null;
    }

    public function setBody(string $body): static
    {
        $this->body = $body;

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

    public function getAuthorId(): int
    {
        return $this->author_id;
    }

    public function setAuthorId(int $author_id): self
    {
        $this->author_id = $author_id;

        return $this;
    }

    public function getAuthor(): ?AuthorEntity
    {
        return $this->author ?? null;
    }

    /**
     * I am allowing null value here so i can test conditions, but all
     * articles should have an author
     */
    public function setAuthor(?AuthorEntity $author): static
    {
        $this->author = $author;

        return $this;
    }
}
