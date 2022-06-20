<?php declare(strict_types=1);

namespace Lightning\Test\Event;

use Lightning\Event\ListenerProvider;

final class PrioritizedListenerProviderTest extends ListenerProviderTest
{
    public function testGetListenersForEventPriority(): void
    {
        $handler1 = function (SomethingHappened $event) {
            $this->assertTrue(true);
        };

        $handler2 = function (SomethingHappened $event) {
            $this->assertTrue(true);
        };

        $provider = new ListenerProvider();

        $provider->addListener(SomethingHappened::class, $handler1, 150)
            ->addListener(SomethingHappened::class, $handler2, 50);

        $this->assertEquals(
            [$handler2,$handler1],
            $provider->getListenersForEvent(new SomethingHappened())
        );
    }
}
