# ORM (Object Relational Mapper)

The Object Relational Mapper extends the `DataMapper` to work with related data, this provides `hasOne`, `hasMany` and `hasAndBelongsToMany` relationships with the `belongsTo` which is for the other side.


Lets create an `Author` model which has many `Articles`.

```php
class Author extends AbstractObjectRelationalMapper
{
    protected $primaryKey = 'id';

    protected string $table = 'authors';

    protected array $fields = [
        'id', 'name', 'created_at','updated_at'
    ];

    protected array $hasMany = [
        'articles' => [
            'class' => Article::class,
            'foreignKey' => 'author_id', // in other table,
            'dependent' => true
        ]
    ];
}

```

Now define the `Article` class

```php
class Article extends AbstractObjectRelationalMapper
{
    protected string $table = 'articles';

    protected array $fields = [
        'id', 'title','body','author_id','created_at','updated_at'
    ];

    protected array $belongsTo = [
        'author' => [
            'class' => Author::class,
            'foreignKey' => 'author_id'
        ]
    ];
}
```

```php
$result = $article->find();
```

This will run the following 2 queries, no matter how many records you are retriving

```sql
SELECT authors.id, authors.name, authors.created_at, authors.updated_at FROM authors LIMIT 1
SELECT articles.id, articles.title, articles.body, articles.author_id, articles.created_at, articles.updated_at FROM articles WHERE articles.author_id IN ( 2000 )
```

And the output is 
```php
^ array:7 [
  "id" => 1000
  "title" => "Article #1"
  "body" => "A description for article #1"
  "author_id" => 2000
  "created_at" => "2021-10-03 09:01:00"
  "updated_at" => "2021-10-03 09:02:00"
  "author" => array:4 [
    "id" => 2000
    "name" => "Jon"
    "created_at" => "2021-10-03 14:01:00"
    "updated_at" => "2021-10-03 14:02:00"
  ]
]
```

## Resources

- [ORM Hate](https://martinfowler.com/bliki/OrmHate.html)