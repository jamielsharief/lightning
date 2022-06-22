<?php declare(strict_types=1);

namespace Lightning\Test\Event;

use PHPUnit\Framework\TestCase;
use Lightning\Event\EventDispatcher;
use Lightning\Event\ListenerProvider;
use Lightning\Event\SubscriberInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class StoppableEvent implements StoppableEventInterface
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

class SubscriberController implements SubscriberInterface
{
    public function registerListeners(ListenerProviderInterface $listenerProvider): void
    {
        $listenerProvider->addListener(StoppableEvent::class, function (SomethingHappened $event) {
            // do something
        });
    }
}

final class EventManagerTest extends TestCase
{
    public function testGetListerProvider(): void
    {
        $provider = new ListenerProvider();
        $dispatcher = new EventDispatcher($provider);
        $this->assertEquals($provider, $dispatcher->getListenerProvider());
    }

    public function testDispatch(): void
    {
        $event = new StoppableEvent();
        $provider = new ListenerProvider();
        $dispatcher = new EventDispatcher($provider);
        $provider->addListener(StoppableEvent::class, function (StoppableEvent $event) {
            $this->assertTrue(true);
        });

        $this->assertEquals($event, $dispatcher->dispatch($event));
    }

    public function testDispatchStopEvent(): void
    {
        $event = new StoppableEvent();
        $provider = new ListenerProvider();
        $dispatcher = new EventDispatcher($provider);
        $provider->addListener(StoppableEvent::class, function (StoppableEvent $event) {
            $this->assertTrue(true);
            $event->stop();
        });

        $provider->addListener(StoppableEvent::class, function (StoppableEvent $event) {
            $this->assertTrue(false); // fail
        });

        $this->assertEquals($event, $dispatcher->dispatch($event));
    }

    public function testSubscribeTo(): void
    {
        $event = new StoppableEvent();
        $provider = new ListenerProvider();
        $dispatcher = new EventDispatcher($provider);

        $this->assertInstanceOf(EventDispatcher::class, $dispatcher->subscribeTo(new SubscriberController()));

        $this->assertCount(1, $provider->getListenersForEvent($event));
    }
}
