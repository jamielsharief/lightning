# Query Builder

An SQL query builder that uses arrays to create select, insert, update and delete statements.

## Usage

Create the SQL Builder object.

```php
$builder = new QueryBuilder(); // don't quote tables and columns etc
$builder = new QueryBuilder('`'); // quote for MySQL
$builder = new QueryBuilder('"'); // quote for postgres or sqlite
```

## Select Statement

To start a `SELECT` query, call the `select` method, this will reset the query

```php
$builder->select(['*'])
    ->from('articles')
    ->where(['status' => 'published']);

$sql = $builder->toString(); // Gets the SQL statement e.g. SELECT * FROM articles WHERE status = :v1
$params = $builder->getParams(); // Gets an array of params [':v1' => 'published']
```

## Conditions

The `where` method accepts an array of conditions.

```php
$builder->select(['*'])
    ->from('articles')
    ->where([
        'published' => 1, // Equals or if you pass a null value then it will be IS NULL
        'author <>' => 1234] // Not equals or you can use != which is non ISO standard,
        'deleted_at <>'  => null, // IS NOT NULL
        'status' => ['new','pending'],  // IN
        'category <>' => ['Development'],  // NOT IN
        'created_at BETWEEN' => ['2021-01-01 12:00:00', '2021-06-01 12:00:00'], // BETWEEN OR NOT BETWEEN
        'title LIKE' => '%foo', // LIKE or NOT LIKE
        'id >=' 2000, // arithmetic operators <,>,<=,>=  
    ]);
```



By default when you call `where` all conditions are joined by `AND` if you want to start a new OR group 

```php
$builder->select(['*'])
    ->from('articles')
    ->where(['status' => 'published']);
    ->or(['author' => 1234]);  // Find status published or author = 1234
```

The array conditions can also use unlimited nested conditions, by using `OR`, `AND`, and `NOT`.

So you can rewrite the query above using nested conditions like this:

```php
$builder->select(['*'])
    ->from('articles')
    ->where([
        'status' => 'published',
        'OR' => [
            'author' => 1234
        ]
    ]);  
```

## Join

To carry out a join query, you can call the join type method

```php
$builder->select(['*'])
    ->from('articles')
    ->leftJoin('authors', 'authors', ['articles.author_id = authors.id'])
```

## Group

To create a `GROUP BY` query

```php
$builder->select(['COUNT(*) AS count','category'])
    ->from('articles')
    ->groupBy('category');
```

## Order

```php
$builder->select(['*'])
    ->from('articles')
    ->orderBy('id DESC');
```

## Having

```php
$builder->select(['id', 'name','email'])
    ->from('articles')
    ->having('COUNT(id) > 5');
```

## Limit

To limit records

```php
$builder->select(['id', 'name','email'])
    ->from('articles')
    ->limit(10);
```

To limit records starting from an offset

```php
$builder->select(['id', 'name','email'])
    ->from('articles')
    ->limit(10, 20);
```
