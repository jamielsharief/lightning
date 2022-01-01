# Collection

A powerful lightweight `Collection` object allowing you work with collections of data quickly.

## Map

Iterate over each item in the `Collection` and returns a new `Collection` using the result that you returned.

```php
$collection = new Collection($contacts);
$contactNames = $collection->map(function($contact){
    return $contact->firstName . ' '. $contact->lastName;
})->toArray();
```

## Reduce

Iterate over each item in the `Collection`  and return a single value

```php
$collection = new Collection([1,2,3]);
$result = $collection->reduce(function ($accumulated, $value) {
    return $accumulated + $value;
}); // 6
```

## Each

Iterate over each item in the `Collection`, return `false` to break.

```php
$collection->each(function($contact){
    $this->log($contact->name);
})->toArray();
```

## Find

Finds the first item that matches the truth test.

```php
$collection->find(function($contact){
   return $contact['status'] === 'active';
});
```

## Filter

Returns a new `Collection` for only the items that returned `true`.

```php
$collection->filter(function($contact){
   return $contact['status'] === 'active';
})->toArray();
```

## Reject

Returns a new `Collection` without the items that returned `true`.

```php
$collection->reject(function($contact){
   return $contact['status'] === 'active';
})->toArray();
```

## Every

Returns true if every item in the `Collection` returned `true`

```php
$collection = new Collection(['a','b','c']);
$result = $collection->every(function ($value) {
            return is_string($value);
        });
```

## Some

Returns true if at least one item in the `Collection` returned `true`

```php
$collection = new Collection(['a', 1]);
$result = $collection->some(function ($value) {
        return is_string($value);
    });
```

## Contains

Returns true if the `Collection` contains a value

```php
$result = $collection->contains('b');
```

## Extract (pluck)

Extracts a single field into a new `Collection`, under the hood this basically a `map` but allows you to use a string based path.

```php
$collection->extract('contact_id')->toArray();
$collection->extract('contact.name')->toArray();
$collection->extract(function($contact){
    return $contact->id;
})->toArray();
```

## Chunk

You can chunk your `Collection` by setting the chunk size.

```php
$collection->chunk(10)
```

## IndexBy

To reindex your `Collection` using a value

```php
$collection->indexBy('id');
$collection->indexBy('address.id');
$collection->indexBy(function($contact){
    return $contact->id;
});
```

## GroupBy

To group data in your `Collection` using a value

```php
$collection->groupBy('status');
$collection->groupBy('user.id');
$collection->groupBy(function($contact){
    return $contact->status;
});
```

## Sort

To sort your `Collection` using the keys

```php
$collection->sort();
$collection->sort(SORT_DESC);
```

## SortBy

To sort your `Collection` using a value

```php
$collection->sortBy('id')
$collection->sortBy('user.id');
$collection->sortBy(function($contact){
    return $contact->status;
});
```


## Avg

To get the average of a property value from your `Collection`

```php
$collection->avg('id')
$collection->avg('user.id');
$collection->avg(function($contact){
    return $contact->id;
});
```

## Median

To get the median of a property value from your `Collection`

```php
$collection->median('id')
$collection->median('user.id');
$collection->median(function($contact){
    return $contact->id;
});
```


## SumOf

To get the sum of a property value from your `Collection`

```php
$collection->sumOf('id')
$collection->sumOf('user.id');
$collection->sumOf(function($contact){
    return $contact->id
});
```

## CountBy

To get a grouped count of a value

```php
$collection->countBy(function ($book) {
    return $book['id'] % 2 == 0 ? 'even' : 'odd';
});
// Example output
[
    'even' => 2,
    'odd' => 1
];
```

## Count

To count the items in your `Collection`

```php
$count = $collection->count();
```

## isEmpty

To check if the `Collection` is empty

```php
$result = $collection->isEmpty();
```

## First 

To get the first item in the `Collection`

```php
$collection->first();
```

## Last 

To get the last item in the `Collection`

```php
$collection->last();
```