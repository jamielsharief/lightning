# Criteria

Criteria provides an SQL like condition searching on arrays of data.

## Usage
 
Create the `Criteria` object passing the conditions.

```php
$criteria = new Criteria([
     'author_id' => 1234,
     'status' => ['draft','active'],
     'category !=' => ['not assigned'], 
     'title LIKE' => 'foo%',
     'created_at BETWEEN' => ['2021-10-01 12:00:00','2021-10-15 00:00:00']
]);

foreach($records as $record) {
    $isMatch = $criteria->match($record);
    // do something
}
```

 