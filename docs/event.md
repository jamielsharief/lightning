# Event

A lightweight [PSR-14: Event Dispatcher](https://www.php-fig.org/psr/psr-14/) implementation with extended `ListenerProviderInterface` called the `ListenerRegistry`.

## Usage

Create the `EventDispatcher` object with the `ListenerRegistry`.

```php
$listenerRegistry  = new ListenerRegistry();
$eventDispatcher = new EventDispatcher($listenerRegistry);
```

Dispatch an event

```php
$event = new CreditCardPaymentAccepted();
$eventDispatcher->dispatch($event);
```

### Listener Registry

To add any `callable` use the `registerListener` method.

```php
$registry  = new ListenerRegistry();
$registry->registerListener(AfterOrder::class, [$this, 'afterOrder']);
```

```php
$registry->registerListener(AfterOrder::class, function(AfterOrder $order){
    // do something
});
```

You can remove a listener like so

```php
$registry->unregisterListener(AfterOrder::class, [$this, 'afterOrder']);
```

## Events

There is a `AbstractEvent` and a `AbstractStoppableEvent` class which you can use as a base for your event objects.

```php
class CustomEvent extends AbstractStoppableEvent
{
    public function __construct(object $source, protected array $payload) 
    {
        parent::__construct($source);
    }

    public function getPayload() : array 
    {
        return $this->payload;
    }
}
```


```php
$event = new CustomEvent($this);
$source = $event->getSource(); // The object where the Event was triggered, e.g. Controller, Model etc
$timestamp = $event->getTimestamp();
$bool = $event->isPropagationStopped(); // AbstractStoppableEvent only
$event->stopPropagation(); // AbstractStoppableEvent only
```

## Listeners

A special `Listner` class is available that would be compatible with other `EventDispatchers` and at the same time allow 

```php
class MyListener extends AbstractListener
{
    public function handle(BeforeSend $event) : void 
    {
        // do something
    }
}
```

# Event Exception

An `EventException` class is marker exception to throw within your Events, they have no special purpose other than identification.