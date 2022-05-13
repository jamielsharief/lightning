# Utilities

A collection of utility components

## Collection

The collection is a super charged object oriented array, with the main array based functions for sorting and maniupation.

```php
$entity = new UserEntity();

$collection = new Collection();

$collection->add($entity);
$collection->remove($entity);
$key = $collection->indexOf($entity);
$bool = $collection->contains($entity);

$first = $collection->get(); // Gets the first available element
$element = $collection->get(5); // gets by key
$bool = $collection->keyExists('foo');

$bool = $collection->isEmpty();
$count = $collection->count();
$collection->clear();

# Sorting
$collection->sort(); // sorts the collection by key
$collection->sort(fn(UserEntity $user) => $user->getId());
$collection->reverse(); // reverses the order the collection is in


$collection->min();
$collection->min(fn(UserEntity $user) => $user->getAge())

$collection->max(); 
$collection->max(fn(UserEntity $user) => $user->getAge())

# Extracting
$collection->slice(0,5); // slice a collection into a new collection
$collection->chunk(10); // chunks the collection into an array of collections

$collection->keys(); // returns just the keys
$collection->values(); // returns just the values of the elements

$collection->toArray();

# For each
$collection->each(function($contact){
    $this->log($contact->name);
})->toArray();

# Map
$collection->map(fn(UserEntity $user) => $user->getId());

# Reduce
$collection->reduce(function ($carry, $value) {
    return $carry + $value;
}); // 6

$collection->reduce(function ($entities, $entity) {
    $entities[$entity->getId()] = $entity;
    return $entities;
}, $initialValue); // use reduce to indexBy

# Filter
$collection->filter(fn(UserEntity $user) => $user->getStatus()  === 'active');
```

## RandomString

A secure random string generator, with various character set constants such as hex, base36, base58, base62, base64, base64 url safe and numeric. Use this to securely generate tokens, passwords, keys, salts etc.

```php
$randomString = new RandomString();

// the default character set is set Base 62 
$randomString->generate(12); // 7nH3XfBYZG5E

$randomString
    ->withCharset(Randomstring::HEX)
    ->generate(32); // 9cee331c6104f9035e57259ec13f7d98

$randomString
    ->withCharset('foobar')
    ->generate(8); // aaborrro

$randomString
    ->withCharset(RandomString::BASE_62 . RandomString::SPECIAL)
    ->generate(32); // 86^PY)f$T06x#KJO

$randomString
    ->withCharset(Randomstring::BASE_64_SAFE)
    ->generate(24); //Go6-dQapORAGBkmY1UQ0eT53
```

## UUID

A simple RFC-4122 compliant UUID v4 generator

```php
$uuid = (new Uuid())->generate(); // 57519f4b-7f7e-4ceb-ae80-a139dc6f07e9
```

There is also a preconfigured constant for matching regular expressions

```php
$this->assertMatchesRegularExpression(Uuid::PATTERN, $uuid);
```
