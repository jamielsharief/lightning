# Entity

An is a lightweight persistance domain object, which usually each entity represents a row in your table in the database.

## Usage

Create your `Entity` class using a singular name, add the properties that represent your data in the table as `private` and then create the setters and getters. 

```php
class User extends AbstractEntity
{
    private ?int $id = null;
    private string $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $value) : static
    {
        $this->name = $value;
        return $this;
    }

    public function getName() : string 
    {
        return $this->name;
    }
}
```