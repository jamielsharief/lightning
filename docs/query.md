# Query

A Lightweight Query builder and statement executor.

## Usage

Create the `PDO` object, it is best to set the error mode to exception mode.

```php
$pdo = new PDO('mysql:host=mysql;port=3306;dbname=lightning','root', 'root', [
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$query new Query($pdo, new SqlBuilder());
```

To load the first record that matches the criteria

```php
$article = $query->select(['id','title','created_at','updated_at'])
    ->from('articles')
    ->where(['id' => 1234])
    ->first();
```

To load all the records

```php
$articles = $query->select(['id','title','created_at','updated_at'])
    ->from('articles')
    ->where(['status' => 'published'])
    ->or(['created_at <' => '2021-10-21 09:00:00'])
    ->all();
```


## Select

To start a `SELECT` query, call the `select` method, this will reset the query

```php
$query->select(['*'])
    ->from('articles')
    ->where(['status' => 'published']);
```

### Conditions

The `where` method accepts an array of conditions.

```php
$query->select(['*'])
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

The array conditions can also use nested conditions if they have the key `OR`, `AND`, this will join conditions by this type

By default when you call `where` all conditions are joined by `AND` if you want to start a new group 

```php
$query->select(['*'])
    ->from('articles')
    ->where(['status' => 'published']);
    ->or(['author' => 1234]);  // Find status published or author = 1234
```

### Join

When you join records, data from each joined table is put in their own `Row` object.

```php
$result = $query->select(['*'])
    ->from('articles')
    ->leftJoin('authors','author', ['articles.author_id = author.id'])
    ->first();

echo $result->title; // article title
echo $result->author->name; // the information from the table authors is in its own property
```

To do multiple joins on the same table, use the aliases

```php
$result = $query->select(['articles.id','title','author.id','author.name'])
    ->from('articles')
    ->leftJoin('authors','author', ['articles.author_id = author.id'])
    ->leftJoin('authors','owner', ['articles.owner_id = owner.id'])
    ->first();

echo $result->author->name; 
echo $result->owner->name; 
```

> When using Postgres or Sqlite with table aliases you must supply the column names, you cannot use wildcard `*`


### Group

To create a `GROUP BY` query

```php
$query->select(['COUNT(*) AS count','category'])
    ->from('articles')
    ->groupBy('category');
```

### Order

```php
$query->select(['*'])
    ->from('articles')
    ->orderBy('id DESC'); // or ['id' => 'DESC']
```

### Having

```php
$query->select(['id', 'name','email'])
    ->from('articles')
    ->having('COUNT(id) > 5');
```

### Limit

To limit records

```php
$query->select(['id', 'name','email'])
    ->from('articles')
    ->limit(10);
```

To limit records starting from an offset

```php
$query->select(['id', 'name','email'])
    ->from('articles')
    ->limit(10, 20);
```


### Paging

You can also use `page` which automatically calculates the limit, offset for you.

```php
$query->select(['*'])
    ->from('articles')
    ->page(5, 20)
```

## Insert Statement

To insert records

```php
$query->insertInto('posts')
->values([
    'title' => 'This is an article',
    'body' => 'Some article body',
    'created_at' => '2021-10-23 15:35:00',
    'updated_at' => '2021-10-23 15:35:00',
])
->execute();
```

To get the last insert ID

```php
$id = $query->getLastInsertId();
```

## Update Statement

To update data in the database, the execute method will return the number of rows affected.

```php
$rowsAffected = $query->update('posts')
    ->set([
        'title' => 'foo'
    ])
    ->where(['id' => 1000])
    ->execute();
```

## Delete Statement

To delete data in the database, the execute method will return the number of rows affected.

```php
$rowsAffected = $query->deleteFrom('posts')
    ->where(['id' => 1000]);
    ->execute();
```
