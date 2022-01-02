# PSR-3 Logger

Simple PSR loggers

# Usage

```php
$logger = new FileLogger(__DIR__ . '/logs/application.log');
$logger->debug('This is a test);
```

```php
$logger = new ConsoleLogger(STDOUT);
$logger->debug('This is a test');
```