<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\Event;

use PHPUnit\Framework\TestCase;
use Lightning\Event\EventDispatcher;
use Lightning\Event\ListenerRegistry;
use Lightning\Event\EventDispatcherAwareTrait;

class BeforeYouGoGo
{
}

class SongsController
{
    use EventDispatcherAwareTrait;

    public function __construct(protected EventDispatcher $eventDispatcher)
    {
    }
}

class DummyEventDispatcher extends EventDispatcher
{
    public function foo()
    {
    }
}

final class EventDispatcherAwareTraitTest extends TestCase
{
    public function testGetEventDispatcher(): void
    {
        $eventDispatcher = new EventDispatcher(new ListenerRegistry());
        $controller = new SongsController($eventDispatcher);
        $this->assertEquals($eventDispatcher, $controller->getEventDispatcher());
    }

    public function testSetEventDispatcher(): void
    {
        $eventDispatcher = new EventDispatcher(new ListenerRegistry());
        $controller = new SongsController($eventDispatcher);
        $clonedDispatcher = clone $eventDispatcher;

        $this->assertEquals($clonedDispatcher, $controller->setEventDispatcher($clonedDispatcher)->getEventDispatcher());
    }

    public function testDispatch(): void
    {
        $eventDispatcher = new EventDispatcher(new ListenerRegistry());
        $controller = new SongsController($eventDispatcher);
        $this->assertInstanceOf(BeforeYouGoGo::class, $controller->dispatchEvent(new BeforeYouGoGo()));
    }

    public function testOn(): void
    {
        $eventDispatcher = new EventDispatcher(new ListenerRegistry());
        $controller = new SongsController($eventDispatcher);
        $event = $controller->registerEventListener(BeforeYouGoGo::class, function (BeforeYouGoGo $event) {
            $this->assertTrue(true);
        })->dispatchEvent(new BeforeYouGoGo());

        $this->assertInstanceOf(BeforeYouGoGo::class, $event);
    }

    public function testOff(): void
    {
        $eventDispatcher = new EventDispatcher(new ListenerRegistry());
        $controller = new SongsController($eventDispatcher);

        $callable = function (BeforeYouGoGo $event) {
            $this->assertTrue(false);
        };
        $event = $controller->registerEventListener(BeforeYouGoGo::class, $callable)
            ->unregisterEventListener(BeforeYouGoGo::class, $callable)
            ->dispatchEvent(new BeforeYouGoGo());

        $this->assertInstanceOf(BeforeYouGoGo::class, $event);
    }
}
