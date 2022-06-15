# PSR-14 Event Dispatcher 

A lightweight [PSR-14: Event Dispatcher](https://www.php-fig.org/psr/psr-14/), with an additional interface to facilate adding and removing listeners.

## Usage

Create the `EventDispatcher`

```php
$eventDispatcher = new EventDispatcher();
```

To add any `callable` use the `addListener` method.

```php
$eventDispatcher->addListener(AfterOrder::class, [$this, 'afterOrder']);
```

Then to dispatch

```php
$eventDispatcher->dispatch(new AfterOrder);
```

You can remove like so

```php
$eventDispatcher->removeListener(AfterOrder::class, [$this, 'afterOrder']);
```

## Priority

The `addListener` method has an optional third argument, the priority, the default number is `10`. Events are sorted from lowest values to highest values and prority is given to events with the lowest number.

```php
$eventDispatcher->addListener(AfterOrder::class, function(AfterOrder $order){
    // do something
}, 50);
```

## Subscribers

Add the `EventSubscriberInterface` to the object

```php
class Controller implements EventSubscriberInterface
{
    public function subscribedEvents(): array
    {
        return [
            SomethingHappened::class => 'foo',
            SomethingElseHappened::class => ['bar', 5]
        ];
    }
```

then  call the `addSubscriber` method

```php
$eventDispatcher->addSubscriber(new Controller());
```

To remove

```php
$eventDispatcher->removeSubscriber(new Controller());
```