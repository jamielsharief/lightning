# PSR-14 Events

Use the` EventManager` to easily inject logic into an application, for more information on the standard see [PSR-14: Event Dispatcher](https://www.php-fig.org/psr/psr-14/).


## Usage

To add any `callable` use the `addListener` method.

```php
$eventManager = new EventManager();
$eventManager->addListener(AfterOrder::class, [$this, 'afterOrder']);
$eventManager->dispatch(new AfterOrder);
```

If you want to listen to multiple events then implement `EventSubscriberInterface` on the object that
you want to do the listening

```php
class OrderListener implements SubscriberInterface
{
    public function subscribedEvents() : array
    {
        return [
            NewOrder::class => 'newOrder',
            AfterOrder::class => ['afterOrder', 5]
        ];
    }

    public function newOrder(NewOrder $event)
    {
        // do something
    }
}
```

Then you would subscribe using the `addSubscriber` and then dispatch.

```php
class ArticlesController() {
    public function initialize() 
    {
        $this->eventManager->addSubscriber($this);
    }
}
```

## Priority

You can also pass a third argument, the priority, the default number is `100`. Events are sorted from lowest values to highest values and prority is given to events with the lowest number.

```php
$eventManager->addListener(AfterOrder::class, function(AfterOrder $order){
    echo "Hello";
}, 50);
```

## Generic Events

There is also a generic `Event` class.

```php
$eventManager = new EventManager();
$eventManager->addListener('Order.afterPayment', [$this, 'afterPayment']);

# To dispatch normally
$event = new Event('Order.afterPayment', $this, ['order' => $order]);
$eventManager->dispatch($event);
```

Subscribers also work nicely with the `Event` class

```php
class OrderListener implements SubscriberInterface
{
    public function subscribedEvents() : array
    {
        return [
            'Order.new' = 'newOrder',
            'Order.after' => ['afterOrder', 40 ]
        ];
    }
}
```