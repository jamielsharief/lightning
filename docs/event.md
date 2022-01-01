# PSR-14 Event Dispatcher 

Use the Event Dispatcher (PSR-14) to easily inject logic into an application, for more information on the standard see [PSR-14: Event Dispatcher](https://www.php-fig.org/psr/psr-14/).


## Usage

To add any `callable` use the `addListener` method.

```php
$eventDispatcher = new EventDispatcher();
$eventDispatcher->addListener(AfterOrder::class, [$this,'afterOrder']);
$eventDispatcher->dispatch(new AfterOrder);
```

If you want to listen to multiple events then implement `EventSubscriberInterface` on the object that
you want to do the listening

```php
class OrderListener implements EventSubscriberInterface
{
    public function getSubscribedEvents() : array
    {
        return [
            NewOrder::class => 'newOrder',
            AfterOrder::class => ['afterOrder', 5]
            PaymentComplete::class => [
                ['logPayment'],
                ['sendSms', 100]
            ]
        ];
    }

    public function newOrder(NewOrder $event)
    {
        
    }
}
```

Then you would subscribe using the `addSubscriber` and then dispatch.

```php
$eventDispatcher = new EventDispatcher();
$eventDispatcher->addSubscriber(new OrderListener);
$eventDispatcher->dispatch(new AfterOrder);
```

## Priority

You can also pass a third argument, the priority, the default number is `10`. Events are sorted from lowest values to highest values and prority is given to events with the lowest number.

```php
$eventDispatcher->addListener(AfterOrder::class, function(AfterOrder $order){
    // do something
},50);
```

## Generic Events

There is also a generic `Event` class.

```php
$eventDispatcher = new EventDispatcher();
$eventDispatcher->addListener('Order.afterPayment', [$this, 'afterPayment']);

# To dispatch normally
$event = new Event('Order.afterPayment', $this, ['order' => $order]);
$eventDispatcher->dispatch($event);
```

Subscribers also work nicely with the `Event` class

```php
class OrderListener implements EventSubscriberInterface
{
    public function getSubscribedEvents() : array
    {
        return [
            'Order.new' = 'newOrder',
            'Order.after' => 'afterOrder'
        ];
    }
}
```