# ORM (Object Relational Mapper)

The Object Relational Mapper extends the `DataMapper` to work with related data. No magic is used.




```php
class Article extends AbstractOrm
{
    protected $primaryKey = 'id';

    protected string $table = 'articles';

    protected array $fields = [
        'id', 'title','body','author_id','created_at','updated_at'
    ];

    protected array $belongsTo = [
        'author' => [
            'className' => Author::class,
            'foreignKey' => 'author_id'
        ]
    ];
}


class Author extends AbstractOrm
{
    protected string $table = 'authors';

    protected array $hasMany = [
        'articles' => [
            'className' => Article::class,
            'foreignKey' => 'author_id', // in other table,
            'dependent' => true
        ]
    ];
}

$result = $article->find();
/*
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
*/
```

## Resources

- [ORM Hate](https://martinfowler.com/bliki/OrmHate.html)