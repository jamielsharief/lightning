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

$bool = $collection->isEmpty();
$count = $collection->count();
$collection->clear();

// These 3 methods you can pass a closure e.g fn(UserEntity $user) => $user->getId(), to customise the value used.
$collection->sort(); // gets a new collection sorted by keys
$collection->min(); // gets the minimum value
$collection->max(); // gets the maximum value

$collection->reverse(); // reverses the order the collection is in

$collection->slice(0,5); // slice a collection
$collection->chunk(10); // chunks the collection into an array of collections

$collection->toArray();
$collection->toList(); // gets the values (without keys);
```

### Each

Iterate over each item in the `Collection`, return `false` to break.

```php
$collection->each(function($contact){
    $this->log($contact->name);
})->toArray();
```

### Map

Iterate over each item in the `Collection` and returns a new `Collection` using the result that you returned.

```php
$collection = new Collection($contacts);
$contactNames = $collection->map(function($contact){
    return $contact->firstName . ' '. $contact->lastName;
})->toArray();
```

### Reduce

Iterate over each item in the `Collection` and return a single value

```php
$collection = new Collection([1,2,3]);
$result = $collection->reduce(function ($carry, $value) {
    return $carry + $value;
}); // 6
```

You can also supply an initial value, so lets say you wanted to index a collection by an id.

```php
$intialValue = []; // entities
$collection->reduce(function ($entities, $entity) {
    $entities[$entity->getId()] = $entity;
    return $entities;
}, $initialValue); // [] 
```

### Filter

Returns a new `Collection` for only the items that returned `true`.

```php
$collection->filter(function($contact){
   return $contact['status'] === 'active';
})->toArray();
```

### Passing closures

The `min`, `max` and `sort` methods also support passing a closure .

```php
$closure = function(Entity $contact){
   return $contact->getAge();
};

$collection->min($closure);
$collection->max($closure);
$collection->sort($closure);
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
