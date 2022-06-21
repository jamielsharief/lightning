# PSR-14 Event Dispatcher 

A lightweight [PSR-14: Event Dispatcher](https://www.php-fig.org/psr/psr-14/) implementation.

## Usage

Create the `EventDispatcher` object with the desired listener provider, we provide the `ListenerProvider` and the `PrioritizedListenerProvider` providers.

```php
$listenerProvider  = new ListenerProvider();
$eventDispatcher = new EventDispatcher($listenerProvider);
```

Dispatch an Event

```php
$event = new CreditCardPaymentAccepted();
$eventDispatcher->dispatch($event);
```


## Listener Provider

To add any `callable` use the `addListener` method.

```php
$provider  = new ListenerProvider();
$provider->addListener(AfterOrder::class, [$this, 'afterOrder']);
```

```php
$provider->addListener(AfterOrder::class, function(AfterOrder $order){
    // do something
});
```

You can remove a listener like so

```php
$provider->removeListener(AfterOrder::class, [$this, 'afterOrder']);
```

You can create a Subscriber class which listens to multiple events by adding the  `SubscriberInterface`.

```php
class Controller implements EventSubscriberInterface
{
    public function subscribedEvents(): array
    {
        return [
            SomethingHappened::class => 'foo',
            SomethingElseHappened::class => 'bar'
        ];
    }
}
```

then  call the `addSubscriber` method

```php
$provider->addSubscriber(new Controller());
```

To remove

```php
$provider->removeSubscriber(new Controller());
```

## PrioritizedListenerProvider

The `PrioritizedListenerProvider` methods `addListener` and `addSubscriber` have an optional third argument, the priority, the default number is `100`. Events are sorted from lowest values to highest values and prority is given to events with the lowest number.

```php
$provider->addListener(AfterOrder::class, [$this, 'afterOrder'], 120);
$provider->addSubscriber(new Controller(), 120);
```