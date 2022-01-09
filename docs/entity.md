# Entity

Provides Entity classes and interfaces. You can use custom entities (setters+getters) or the generic entity (use set/get method) which can also be customised.

## Usage

Create your `Entity` class using a singular name. 

```php
class User extends AbstractEntity
{
    private ?int $id = null;
    private string $name;


    public static function fromState(array $state): Article
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

    public function getId() : int 
    {
        return $this->id;
    }
}
```

```php
class Article
{
    public function __construct(private PDO $pdo)
    {
    }

    public function find(): ?EntityInterface
    {
        $result = $this->query('SELECT * FROM articles LIMIT 1');

        return $result ? $this->mapRow($result) : null;
    }

    public function findById(int $id): ?EntityInterface
    {
        $result = $this->query('SELECT * FROM articles WHERE id = :id', ['id' => $id]);

        return $result ? $this->mapRow($result) : null;
    }

    public function findAll(): iterable
    {
        return $this->mapRows(
            $this->queryAll('SELECT * FROM articles')
        );
    }

    public function mapRow(array $row) : Article
    {
         return Article::fromState($row);
    }

    public function mapRows(array $rows) : array 
    {
        return array_map(function($row){
            return $this->mapRow($row);
        },$rows);
    }

    protected function query(string $sql, array $params = []): ?array
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetch() ?: null;
    }

    protected function queryAll(string $sql, array $params = []): ?array
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll() ?: [];
    }
    ....
}
```