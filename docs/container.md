# PSR-11: Dependency Injection Container

Superfast lightweight DI container with autowiring and autoconfiguration support.

## Usage

Create a a definitions file, and configure the Services using inline factory methods

```php
return [
    LoggerInterface::class => function() {
        return new Logger(__DIR__ . '/logs/application.log');
    },
    Spider::class => function(ContainerInterface $container) {
        $logger = $container->get(LoggerInterface::class);
        return new Spider($logger, true);
    }
];
```

Create the `Container`

```php
$services = include __DIR__ . '/config/services.php';
$container  = new Container($services);
```

To retrieve a service from the `Container`

```php
$spider = $container->get(Spider::class);
```

## Registering Services

Typically you would load services from the definitions, which under the hood registers them these functions.

```php
// You can add a service to be managed by the container like this
$container->register(Logger::class);

// To configure how a service will be created or call setter methods , you can use an inline factory method
$container->register(Database::class, function(ContainerInterface $container){
    $logger = $container->get(LoggerInterface::class);
    return new Database($logger, env('DB_USERNAME'), env('DB_PASSWORD'));
}))

// If you already have the singleton instance, then you can add it like this
$container->register(CacheInterface::class, new Cache());
```

By default an object is only resolved once when you call `get` and then the same object will be returned going foward, if you need to create a new object from the class or factory method.

```php
$container->resolve(CacheInterface::class)
```

## Autowiring

To enable autowiring

```php
$container->enableAutowiring();
```

## Autoconfigure

You can configure the `Container` to automatically manage services, so if the class exists it will try to resolve it.

```php
$container->enableAutoConfigure();
```