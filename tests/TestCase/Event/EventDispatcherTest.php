<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\Event;

use PHPUnit\Framework\TestCase;
use Lightning\Event\EventDispatcher;
use Lightning\Event\ListenerRegistry;
use Psr\EventDispatcher\StoppableEventInterface;

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

final class EventDispatcherTest extends TestCase
{
    public function testGetListerProvider(): void
    {
        $provider = new ListenerRegistry();
        $dispatcher = new EventDispatcher($provider);
        $this->assertEquals($provider, $dispatcher->getListenerRegistry());
    }

    public function testDispatch(): void
    {
        $event = new StoppableEvent();
        $provider = new ListenerRegistry();
        $dispatcher = new EventDispatcher($provider);
        $provider->registerListener(StoppableEvent::class, function (StoppableEvent $event) {
            $this->assertTrue(true);
        });

        $this->assertEquals($event, $dispatcher->dispatch($event));
    }

    public function testDispatchStopEvent(): void
    {
        $event = new StoppableEvent();
        $provider = new ListenerRegistry();
        $dispatcher = new EventDispatcher($provider);
        $provider->registerListener(StoppableEvent::class, function (StoppableEvent $event) {
            $this->assertTrue(true);
            $event->stop();
        });

        $provider->registerListener(StoppableEvent::class, function (StoppableEvent $event) {
            $this->assertTrue(false); // fail
        });

        $this->assertEquals($event, $dispatcher->dispatch($event));
    }
}
