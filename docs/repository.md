# Repository (concept)

This is an additional layer over the `DataMapper`.

## Usage

```php
$pdo = new PDO('mysql:host=mysql;port=3306;dbname=lightning','root', 'root');
$dataSource = new DatabaseDataSource($pdo, new QueryBuilder());

$repository = new UserRepository( new UserMapper($dataSource));
```

