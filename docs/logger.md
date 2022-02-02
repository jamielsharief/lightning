# PSR-3 Logger

Simple PSR loggers

# Usage

## File Logger

```php
$logger = new FileLogger(__DIR__ . '/logs/application.log');
$logger->debug('This is a test);
```

## Console Logger
```php
$logger = new ConsoleLogger(STDOUT);
$logger->debug('This is a test');
```


## Logger

Stack loggers

```php
$logger = new Logger();
$logger->pushLogger($fileLogger1);
$logger->pushLogger($fileLogger2);
```