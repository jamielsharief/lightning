<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\Event;

use PHPUnit\Framework\TestCase;
use Lightning\Event\ListenerRegistry;

abstract class AbstractEvent
{
    public string $data = '';
}

class SomethingHappened extends AbstractEvent
{
}

class SomethingElseHappened extends AbstractEvent
{
}

class ListenerRegistryTest extends TestCase
{
    public function testAddListener(): void
    {
        $handler = function (SomethingHappened $event) {
            $this->assertTrue(true);
        };

        $provider = new ListenerRegistry();
        $this->assertInstanceOf(ListenerRegistry::class, $provider->registerListener(SomethingHappened::class, $handler));
        $this->assertCount(1, $provider->getListenersForEvent(new SomethingHappened()));
    }

    public function testGetListenersForEvent(): void
    {
        $handler = function (SomethingHappened $event) {
            $this->assertTrue(true);
        };

        $this->assertEquals(
            [$handler],
            (new ListenerRegistry())->registerListener(SomethingHappened::class, $handler)->getListenersForEvent(new SomethingHappened())
        );
    }

    /**
     * @depends testAddListener
     */
    public function testRemoveListener(): void
    {
        $handler = function (SomethingHappened $event) {
            $this->assertTrue(true);
        };
        $provider = (new ListenerRegistry())->registerListener(SomethingHappened::class, $handler);

        $this->assertInstanceOf(ListenerRegistry::class, $provider->unregisterListener(SomethingHappened::class, $handler));
        $this->assertCount(0, $provider->getListenersForEvent(new SomethingHappened()));
    }
}
