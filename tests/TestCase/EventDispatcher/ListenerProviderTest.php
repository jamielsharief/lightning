<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\EventDispatcher;

use PHPUnit\Framework\TestCase;
use Lightning\EventDispatcher\ListenerProvider;

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
