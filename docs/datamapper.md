# DataMapper

DataMapper component implements the [Data Mapper Pattern](https://martinfowler.com/eaaCatalog/dataMapper.html), this uses the `Entity` and `QueryBuilder` components.

## Example

Create your `DataMapper`, ensuring that you add the `table`, `fields` properties and the `mapDataToEntity` method.

```php
/**
 * Article Mapper
 * 
 * @method ?ArticleEntity find(QueryObject $query)
 * @method ?ArticleEntity findBy(array $criteria, array $options = [])
 * @method ArticleEntity[] findAll(QueryObject $query)
 * @method ArticleEntity[] findAllBy(array $criteria, array $options = [])
 */
class Article extends AbstractDataMapper
{
    protected $primaryKey = 'id';
    protected string $table = 'articles';
    protected array $fields = [
        'id', 'title','body','author_id','created_at','updated_at'
    ];

    public function mapDataToEntity(array $data): EntityInterface
    {
        return ArticleEntity::fromState($data);
    }
}
```

Finding records, this under the hood uses the `QueryBuilder` component.

```php
$entity = $article->findBy(['id' => 1000]);
$entities = $article->findAllBy(['status' => 'new']);
$count = $article->findCountBy(['status' => 'new']);
$ids = $article->findListBy(['status <>' => 'draft']);
$statuses = $article->findListBy(['status <>' => 'draft'],[
    'keyField'=> 'id', 'valueField' => 'status'
]);
$grouped = $article->findListBy(['status <>' => 'draft'],[
    'keyField'=> 'id', 'valueField' => 'title' ,'groupField' => 'status' 
    ]);
```

You can carry out bulk operations, remember these don't trigger `events` or `hooks`.

```php
$count = $article->updateAllBy(
    ['status'=>'pending','owner'=> 1234], 
    ['status'=>'approved']
);
$count $aritcle->deleteAllBy([
    'status'=>'draft',
    'created_date <' => date('Y-m-d H:i:s',strtotime('- 3 months'))
]);
```

## Hooks

The following hooks can be intercepted

- beforeSave  - triggered before beforeCreate or beforeUpdate
- beforeCreate - triggered on save if the operation is a create
- beforeUpdate - triggered on save if the operation is an update
- beforeDelete
- afterCreate - triggered on save if the operation was a create
- aterUpdate - triggered on save if the operation was an update
- afterSave - triggered after afterCreate or afterUpdate
- afterDelete
- beforeFind - triggered on find, findCount and findList
- afterFind - triggered on find and findList

To register a Hook, in the `DataMapper`

```php

protected function initialize() : void 
{
    $this->registerHook('beforeFind', 'doSomething')
}

public function doSomething(EntityInterface $entity) : bool 
{
    $entity->foo = 'bar';
}
```

## PSR-14 Events

The following Events are triggered

- BeforeSave  - triggered before beforeCreate or beforeUpdate
- BeforeCreate - triggered on save if the operation is a create
- BeforeUpdate - triggered on save if the operation is an update
- BeforeDelete
- AfterCreate - triggered on save if the operation was a create
- AterUpdate - triggered on save if the operation was an update
- AfterSave - triggered after afterCreate or afterUpdate
- AfterDelete
- BeforeFind
- BeforeFind - triggered on find, findCount and findList
- AfterFind - triggered on find and findList