# Database

Database components come with `PdoFactory`, `Connection` and a `QueryBuilder`.

## PDO Factory

Creates and configures PDO object to in a standard and secure way.

```php
use Lightning\Database\PdoFactory;
$pdoFactory = new PdoFactory('mysql:host=127.0.0.1;port=3306;dbname=lightning', 'root', 'secret');
$pdo = $pdoFactory->create();
```

The default fetch mode for the `PDO` object to an associative array, however if you want to change it to objects.

```php
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_OBJ);
```

You maybe also want the `PdoFactory` to work differently

```php
class CustomPdoFactory implements PdoFactoryInterface
{
    public function create(): PDO
    {
        return new PDO(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'));
    }
}
```

## Connection

Works with PDO and PSR-3 Logger, also allows you to connect and disconnect as needed.

### Usage

```php
$pdoFactory = new PdoFactory('mysql:host=mysql;port=3306;dbname=lightning', 'root', 'root'); // 
$db = new Connection($pdoFactory);
$db->connect();
$db->disconnect();
```

```php
$statement = $db->execute('SELECT * FROM articles')
foreach($statement as $row){
    dd($row);
}
```

> Placholders values don't have to be quoted

To fetch a single record, using positional `?` placeholders

```php
$row = $db->execute('SELECT * FROM articles WHERE id = ? LIMIT 1', [1000])
        ->fetch();
```

To fetch multiple records, using named `:name` placeholders

```php
$rows = $db->execute('SELECT * FROM articles WHERE id = :id ', ['id' => 1000])
        ->fetchAll();
```

You can also pass any query object that implements `Stringable`, such as the `QueryBuilder` object.

```php
$query = (new QueryBuilder())->select('*')
                ->from('users')
                ->where('id = :id','active = :active');
                
$users = $db->execute($query, ['id' => 1000,'status' => 'active'])->fetchAll();
```

### Insert/Update/Delete

To `insert` a row into the database

```php
$db->insert('articles', [
    'title' => 'This is an article'
]);
```

To `update` a row or rows in the database, with the id values

```php
$db->update('articles', [
    'title' => 'This is an article'
], ['id' => 1234]);
```

To `delete` a row or rows in the database, with the id values

```php
$db->delete('articles',['id' => 1234]);
```

## Row

The `Row` object can be used with `PDO`, this is an object with array access, and some other handy features
when working with a result from the database.

```php
$row = $connection->execute('SELECT * FROM articles')->fetchObject(Row::class);
$title = $row->title;
$title = $row['title'];
```

## Resources

- [https://phpdelusions.net/pdo](https://phpdelusions.net/pdo)
