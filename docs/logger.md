# PSR-3 Logger

A lightweight PSR-3 Logger component.

## Usage

```php
$logger = new Logger('application');
$logger->addHandler(new FileHandler('/var/www/logs/application.log'));

// then use the PSR-3 Logging methods
$logger->log(LogLevel::ERROR, 'An error has occured');
```

## Handling

When creating `handler` you can set the minimum log level to be used, if the handler is called but the log level is less, then it will
not log anything.

```php
$handler = new FileHandler('/var/www/logs/application.log', LogLevel::WARNING);
```

## Custom Handlers

Create your own custom handler with ease.

```php
class CustomHandler extends AbstractHandler
{
    public function handle(LogMessage $message, string $level, string $channel, DateTimeImmutable $dateTime): bool
    {
        return true;
    }
}
```
