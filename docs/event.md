# Event

A lightweight [PSR-14: Event Dispatcher](https://www.php-fig.org/psr/psr-14/) implementation with extended `ListenerRegistryInterface` called `ListenerRegistry`.

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

To add any `callable` use the `addListener` method.

```php
$provider  = new ListenerRegistry();
$provider->registerListener(AfterOrder::class, [$this, 'afterOrder']);
```

```php
$provider->registerListener(AfterOrder::class, function(AfterOrder $order){
    // do something
});
```

You can remove a listener like so

```php
$provider->unregisterListener(AfterOrder::class, [$this, 'afterOrder']);
```