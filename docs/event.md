# PSR-14 Event Dispatcher 

A lightweight [PSR-14: Event Dispatcher](https://www.php-fig.org/psr/psr-14/) implementation.

## Usage

### Event Dispatcher

Create the `EventDispatcher` object with the desired listener provider, we provide the `ListenerProvider` and the `PrioritizedListenerProvider` providers.

```php
$listenerProvider  = new ListenerProvider();
$eventDispatcher = new EventDispatcher($listenerProvider);
```

Dispatch an event

```php
$event = new CreditCardPaymentAccepted();
$eventDispatcher->dispatch($event);
```

### Listener Provider

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
class Controller implements SubscriberInterface
{
    public function registerListeners(ListenerProviderInterface $listenerProvider): void
    {
        $listenerProvider->addListener(SomethingHappened::class, function (SomethingHappened $event) {
            // do something
        });
    }
}
```

Then the call the `subscribeTo` method  `EventDispatcher` when the object is constructed.

```php
$dispatcher->subscribeTo(new ArticlesController());
```

### PrioritizedListenerProvider

The `PrioritizedListenerProvider` method `addListener` has  an optional third argument, the priority, the default number is `100`. Events are sorted from lowest values to highest values and prority is given to events with the lowest number.

```php
$provider->addListener(AfterOrder::class, [$this, 'afterOrder'], 120);
```