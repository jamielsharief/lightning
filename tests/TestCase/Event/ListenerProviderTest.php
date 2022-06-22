<?php declare(strict_types=1);

namespace Lightning\Test\Event;

use PHPUnit\Framework\TestCase;
use Lightning\Event\ListenerProvider;

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

class ListenerProviderTest extends TestCase
{
    public function testAddListener(): void
    {
        $handler = function (SomethingHappened $event) {
            $this->assertTrue(true);
        };

        $provider = new ListenerProvider();
        $this->assertInstanceOf(ListenerProvider::class, $provider->addListener(SomethingHappened::class, $handler));
        $this->assertCount(1, $provider->getListenersForEvent(new SomethingHappened()));
    }

    public function testGetListenersForEvent(): void
    {
        $handler = function (SomethingHappened $event) {
            $this->assertTrue(true);
        };

        $this->assertEquals(
            [$handler],
            (new ListenerProvider())->addListener(SomethingHappened::class, $handler)->getListenersForEvent(new SomethingHappened())
        );
    }

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

    /**
     * @depends testAddListener
     */
    public function testRemoveListener(): void
    {
        $handler = function (SomethingHappened $event) {
            $this->assertTrue(true);
        };
        $provider = (new ListenerProvider())->addListener(SomethingHappened::class, $handler);

        $this->assertInstanceOf(ListenerProvider::class, $provider->removeListener(SomethingHappened::class, $handler));
        $this->assertCount(0, $provider->getListenersForEvent(new SomethingHappened()));
    }
}
