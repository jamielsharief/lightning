<?php declare(strict_types=1);

namespace Lightning\Test\Event;

use Lightning\Event\Event;
use PHPUnit\Framework\TestCase;
use Lightning\Event\EventDispatcher;
use Lightning\Event\EventSubscriberInterface;
use Psr\EventDispatcher\StoppableEventInterface;

class TestEvent
{
}

class TestStoppableEvent implements StoppableEventInterface
{
    public bool $isStopped = false;

    public function stop(): void
    {
        $this->isStopped = true;
    }
    public function isPropagationStopped(): bool
    {
        return $this->isStopped;
    }
}

class Controller implements EventSubscriberInterface
{
    protected array $events = [];

    public function getSubscribedEvents(): array
    {
        return [
            TestEvent::class => 'catchEvent',
            TestStoppableEvent::class => ['catchEvent', 2],
        ];
    }

    public array $methodsCalled = [];

    /**
     * Event Listener for testing
     *
     * @param object  $event
     * @return void
     */
    public function catchEvent(object $event): void
    {
        $this->events[] = $event;
    }

    public function getEvents(): array
    {
        return $this->events;
    }
}

final class EventDispatcherTest extends TestCase
{
    public array $events = [];

    public function setUp(): void
    {
        $this->events = [];
    }

    public function testAddListener(): void
    {
        $dispatcher = new EventDispatcher();
        $callable = [$this,'catchEvent'];

        $this->assertInstanceOf(
            EventDispatcher::class,
            $dispatcher->addListener(TestEvent::class, $callable)
        );
        $this->assertSame([$callable], $dispatcher->getListenersForEvent(new TestEvent()));
    }

    public function testAddSubscriber(): void
    {
        $dispatcher = new EventDispatcher();
        $subscriber = new Controller();

        $this->assertInstanceOf(
            EventDispatcher::class,
            $dispatcher->addSubscriber($subscriber)
        );

        $callable = [$subscriber,'catchEvent'];
        $this->assertSame([$callable], $dispatcher->getListenersForEvent(new TestEvent()));

        $this->assertSame([
            [$subscriber,'catchEvent'],
        ], $dispatcher->getListenersForEvent(new TestEvent()));
    }

    /**
     * @depends testAddListener
     */
    public function testGetListenersForEvent(): void
    {
        $dispatcher = new EventDispatcher();
        $this->assertEquals([], $dispatcher->getListenersForEvent($this));

        $event = new TestEvent();
        $callable = [$this,'catchEvent'];

        $dispatcher->addListener(TestEvent::class, $callable);
        $this->assertSame([$callable], $dispatcher->getListenersForEvent($event));
    }

    /**
     * @depends testAddListener
     */
    public function testGetListenersForEventPriority(): void
    {
        $dispatcher = new EventDispatcher();
        $event = new TestEvent();
        $subscriber = new Controller();

        $dispatcher->addSubscriber($subscriber);
        $dispatcher->addListener(TestEvent::class, [$this,'catchEvent'], 1);

        $listeners = $dispatcher->getListenersForEvent($event);

        $this->assertEquals([$this,'catchEvent'], $listeners[0]);
        $this->assertEquals([$subscriber,'catchEvent'], $listeners[1]);
    }

    /**
     * @depends testAddListener
     */
    public function testGetListenersForEventPrioritySubscriber(): void
    {
        $dispatcher = new EventDispatcher();
        $event = new TestStoppableEvent();
        $subscriber = new Controller();

        $dispatcher->addListener(TestStoppableEvent::class, [$this,'catchEvent']);
        $dispatcher->addSubscriber($subscriber);

        $listeners = $dispatcher->getListenersForEvent($event);

        $this->assertEquals([$subscriber,'catchEvent'], $listeners[0]);
        $this->assertEquals([$this,'catchEvent'], $listeners[1]);
    }

    public function testRemoveListener(): void
    {
        $dispatcher = new EventDispatcher();

        $callable = [$this,'catchEvent'];
        $dispatcher->addListener(TestEvent::class, $callable);
        $this->assertCount(1, $dispatcher->getListenersForEvent(new TestEvent()));

        $this->assertInstanceOf(EventDispatcher::class, $dispatcher->removeListener(TestEvent::class, $callable));
        $this->assertCount(0, $dispatcher->getListenersForEvent(new TestEvent()));
    }

    public function testRemoveSubscriber(): void
    {
        $dispatcher = new EventDispatcher();

        $subscriber = new Controller();
        $dispatcher->addSubscriber($subscriber);

        $this->assertCount(1, $dispatcher->getListenersForEvent(new TestEvent()));
        $this->assertInstanceOf(EventDispatcher::class, $dispatcher->removeSubscriber($subscriber));
        $this->assertCount(0, $dispatcher->getListenersForEvent(new TestEvent()));
    }

    public function testDispatch(): void
    {
        $dispatcher = new EventDispatcher();
        $event = new TestEvent();
        $this->assertEquals($event, $dispatcher->dispatch($event));
    }

    public function testDispatchNoEvents(): void
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->dispatch(new TestEvent());
        $this->assertCount(0, $this->events);
    }

    public function testDispatchSingleEvent(): void
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(TestEvent::class, [$this,'catchEvent']);

        $dispatcher->dispatch(new TestEvent());

        $this->assertCount(1, $this->events);
    }

    public function testDispatchStoppableEvent(): void
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(TestStoppableEvent::class, [$this,'stopEvent']);
        $dispatcher->addListener(TestStoppableEvent::class, [$this,'catchEvent']);

        $dispatcher->dispatch(new TestStoppableEvent());

        $this->assertCount(1, $this->events);
    }

    /**
    * Event Listener for testing
    *
    * @param object  $event
    * @return void
    */
    public function catchEvent(object $event): void
    {
        $this->events[] = $event;
    }

    public function stopEvent(TestStoppableEvent $event): void
    {
        $event->stop();
        $this->events[] = $event;
    }
}
