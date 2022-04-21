# PSR-16 Cache

Simple lightweight PSR cache libraries, `File`, `Redis`, `Apcu` and `Memory`.


## Usage

Create the `Cache` object

```php
$cache = new FileCache(__DIR__ '/tmp/cache');
// to set
$cache->set('foo','bar');
// check if the value is in cache
$bool = $cache->has('foo');

// gets a value from the cache
$result = $cache->get('foo');

// deletes a value from the cache
$cache->delete('foo');

// clears the cache
$cache->clear();
```

Other `CacheInterface` methods include `getMultiple`, `setMultiple` and `deleteMultiple`.

## File Cache

To create a new `File` cache object simple pass the directory where data will be cached.

```php
$cache = new FileCache(__DIR__ '/tmp/cache');
```

## Redis Cache

To use the `Redis` cache you will need to have the PHP extension `php-redis `installed and a `Redis` server setup.

```php
$connection = new Redis;
$connection->pconnect('127.0.0.1', 6379);

$cache = new RedisCache($connection);
```

## Apcu Cache

To use Apcu you need to have the `Apcu extension` installed with `apc.enbabled` and `apc.enable_cli` in your `php.ini` files. Then to create a `Apcu` cache object, you will need to have

```php
$cache = new ApcuCache();
```

## Memory Cache

The memory cache is ideal for testing or to just cache during during the request.

```php
$cache = new MemoryCache();
```