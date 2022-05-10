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
$count = $aritcle->deleteAllBy([
    'status'=>'draft',
    'created_date <' => date('Y-m-d H:i:s',strtotime('- 3 months'))
]);
```

## Query Object

Under the hood, the find methods use the `QueryObject`, this object is passed to the `Events` and `Hooks`.

```php
$query = new QueryObject(['status' => 'pending'],['order' => 'title DESC']);
$result = $mapper->find($query);
$result = $mapper->findAll($query);
$result = $mapper->findCount($query);
$mapper->deleteAll($query);
$mapper->updateAll($query, ['status'=> 'approved']);
```

## ResultSet

During the find query the results from the database query are in the `ResultSet` object, and this is passed around to `Events` and `Hooks` and make it easy for modification.

```php
function addFoo(ResultSet $resultSet) { 
    foreach($resultSet as $key => $row){
        $resultSet[$key]['foo'] = 'bar';
    }
}
```

The `ResultSet` object implements `ArrayAccess` and `Countable` and offers some basic methods for working with a result collection.

```php
$resultSet->isEmpty();
$row = $resultSet->first();
$array = $resultSet->toArray();
$string = $resultSet->toString();
```

There are also collection like methods to help manipulate collections of records.

### Map

```php
$mappedResultSet = $resultSet->map(function($row){
    $row['status'] = 'active';
    return $row;
});
```

### Filter

```php
$filteredResultSet = $resultSet->filter(function($row){
    return $row['status'] === 'active';
});
```

### IndexBy

```php
$indexedResultSet = $resultSet->indexBy(function($row){
    return $row['id'];
});
```

### GroupBy

```php
$groupedResultSet = $resultSet->groupBy(function($row){
    return $row['status'];
});
```

## Hooks

The `DataMapper` offers hooks to allow you modify the behavior of the `DataMapper`. The following hooks can be intercepted

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

## Entity Lifecycle Events

The `DataMapper` also supports entity lifecycle events, attach one of the `Entity\Callaback` interfaces to you entity and these will be called.

e.g 

```php
class User extends AbstractEntity implements BeforeSaveInterface
{
    public function beforeSave() : void 
    {
        // do something
    }
}

```

## PSR-14 Events

You can also use PSR-14 Events, the following events are triggered:

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