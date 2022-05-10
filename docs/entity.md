# Entity

An is a lightweight persistance domain object, which usually each entity represents a row in your table in the database.

## Usage

Create your `Entity` class using a singular name, add the properties that represent your data in the table as `private` and then create the setters and getters. 

When working with entities, unless the datasource allows a `null` value do not set a default `null` value just for the sake of it, that is incorrect.

```php
class User extends AbstractEntity
{
    private int $id;
    private string $email;
    private string $password;
    private string $created_at;
    private string $updated_at;

    // hook called when the entity is created
    protected function initialize(): void
    {
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getEmail(): ?string
    {
        return $this->email ?? null;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password ?? null;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

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
}
```