# ORM (Object Relational Mapper)

The Object Relational Mapper extends the `DataMapper` to work with related data, this provides `hasOne`, `hasMany` and `belongsToMany` relationships with the `belongsTo` which is for the other side.

## Usage

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
$result = $article->findBy(['id'=>1000], ['with'=> [Author::class]]);
```


This will run the following 2 queries, no matter how many records you are retriving

```sql
SELECT authors.id, authors.name, authors.created_at, authors.updated_at FROM authors LIMIT 1
SELECT articles.id, articles.title, articles.body, articles.author_id, articles.created_at, articles.updated_at FROM articles WHERE articles.author_id IN ( 2000 )
```

The related `Author` is added to the `Article`

```php
[
  'id' => 1000
  'title' => 'Article #1'
  'body' => 'A description for article #1'
  'author_id' => 2000
  'created_at' => '2021-10-03 09:01:00'
  'updated_at' => '2021-10-03 09:02:00'
  'author' => [
    'id' => 2000
    'name' => 'Jon'
    'created_at' => '2021-10-03 14:01:00'
    'updated_at' => '2021-10-03 14:02:00'
  ]
]
```

## Associations

### Has One (one-to-one)

The `hasOne` association is a `one-to-one` relationship, for example: a user has one profile. The foreign key is in the other table, so the profiles table has the column `user_id`.

The following options are supported:
- class: class for the Object Relational Mapper 
- foreignKey: the name of the foreign key in the other table
- dependent: When set to true associated records will also be deleted


## Has Many (one-to-many)

The  `hasMany`  association is `one-to-many` relationship, for example: a user has many contacts.  The foreign key is in the other table, so the contacts table has the column `user_id`.

The following options are supported:
- class: class for the Object Relational Mapper 
- foreignKey: the name of the foreign key in the other table
- dependent: When set to true associated records will also be deleted

## BelongsTo (many-to-one)

The `belongsTo` association is a `many-to-one` relationship, for example: many contacts belong to a user. The foreign key is in the current table, so the contacts table has the column `user_id`.

- class: class for the Object Relational Mapper 
- foreignKey: the name of the foreign key in the current table

## BelongsToMany (many-to-many)

The `belongsToMany` association is a `many-to-many` relationship, for example: tags belongs to many articles, and articles belongs to many tags. This is handled by a special join table

```sql
CREATE TABLE `posts_tags` (
  `post_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY  (`post_id`,`tag_id`)
);
```

The following options are supported:
- class: class for the Object Relational Mapper
- joinTable: the name of the join table
- foreignKey: the name of the foreign key that references the current mapper
- associatedForeignKey: the name of the foreign key that references the other mapper
- dependent: When set to true when records are removed, related records from the join table will be deleted


## MapperManager

The DataMapper manager is responsible for managing the mapper instances and creating them when needed, ensuring that there is only one instance ever created.

To create the Manager in your DI container

```php
$manager = new MapperManager($dataSource);
```

If you are adding additional depenendices to the constructor or using a different datasource with a particular mapper, then you will need to either configure how the `DataMapper` is created or add an already created one.

To add an already created one

```php
$manager->add(new ArticleMapper(new MemoryDataSource()));
```

To create one when it is needed aka lazy load, you can use a factory callable.

```php
$manager->configure(ArticleMapper::class, function(DataSourceInterface $dataSource, MapperManager $manager){
    return new ArticleMapper($dataSource, $manager, new SomeDependency());
});
```



## Resources

- [ORM Hate](https://martinfowler.com/bliki/OrmHate.html)