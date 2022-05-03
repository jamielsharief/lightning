# Entity

Provides Entity classes and interfaces. You can use custom entities (setters+getters) or the generic entity (use set/get method) which can also be customised.

## Usage

Create your `Entity` class using a singular name, configure the `fromState` method and the `toArray` method, then create setters and getters for each property.

```php
class User extends AbstractEntity
{
    private ?int $id = null;
    private string $name;

    public static function fromState(array $state): User
    {
        // Check state
        $user = new static();
        
        if(isset($state['id'])){
            $user->id = $state['id'];
        }

        $user->setName($state['name']);

        return $user;
    }

    // Gets this object state as an array which is sent to storage
    public function toArray(): array
    {
        $id = $this->id ? ['id' => $this->id] : [];

        return $id + [
            'name' => $this->name
        ];
    }

    public function setName(string $value) : void
    {
        $this->name = $value;
    }

    public function getName() : string 
    {
        return $this->name;
    }

     public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }
}
```