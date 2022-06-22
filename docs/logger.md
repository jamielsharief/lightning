# PSR-3 Logger

A lightweight PSR-3 Logger component.

## Usage

```php
$logger = new Logger('application');
$logger->addHandler(new FileHandler('/var/www/logs/application.log'));
$logger->log(LogLevel::ERROR,'An error has occured');
```

## Handling

When creating `handler` you can set the minimum log level to be used, if the handler is called but the log level is less, then it will
not log anything.

```php
$handler = new FileHandler('/var/www/logs/application.log', LogLevel::WARNING);
```

## Custom Handlers

Using the ` HandlerInterface` you can create your own log handlers.

```php
class CustomHandler extends implements HandlerInterface
{
    public function handle(string $level, LogMessage $message, DateTimeImmutable $dateTime, string $channel): bool
    {
        return true;
    }

    public function isHandling(string $level) : bool 
    {
        return true;
    }
}
```