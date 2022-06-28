<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\EventDispatcher;

use Lightning\EventDispatcher\EventDispatcher;
use Lightning\EventDispatcher\PrioritizedListenerProvider;

final class PrioritizedListenerProviderTest extends ListenerProviderTest
{
    public function testGetListenersForEventPriority(): void
    {
        $handler1 = function (SomethingHappened $event) {
            fwrite(STDOUT, 'handler1');
        };

        $handler2 = function (SomethingHappened $event) {
            fwrite(STDOUT, 'handler2');
        };

        $provider = new PrioritizedListenerProvider();

        $provider->addListener(SomethingHappened::class, $handler2, 60)
            ->addListener(SomethingHappened::class, $handler1, 40);

        // $dispatcher = new EventDispatcher($provider);
        // $dispatcher->dispatch(new SomethingHappened());

        $this->assertEquals(
            [$handler2,$handler1],
            $provider->getListenersForEvent(new SomethingHappened())
        );
    }
}
