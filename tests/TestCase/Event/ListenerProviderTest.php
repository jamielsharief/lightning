<?php declare(strict_types=1);

namespace Lightning\Test\Event;

use PHPUnit\Framework\TestCase;
use Lightning\Event\ListenerProvider;
use Lightning\Event\SubscriberInterface;

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

class Controller implements SubscriberInterface
{
    public function subscribedEvents(): array
    {
        return [
            SomethingHappened::class => 'foo',
            SomethingElseHappened::class => 'bar'
        ];
    }

    public function foo(object $event): void
    {
        $event->data .= 'foo';
    }

    public function bar(object $event): void
    {
        $event->data .= 'bar';
    }
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

    public function testAddSubscriber(): void
    {
        $subscriber = new Controller();

        $provider = new ListenerProvider();
        $this->assertInstanceOf(ListenerProvider::class, $provider->addSubscriber($subscriber));
        $this->assertCount(1, $provider->getListenersForEvent(new SomethingHappened()));
        $this->assertCount(1, $provider->getListenersForEvent(new SomethingElseHappened()));
    }

    /**
     * @depends testAddSubscriber
     */
    public function testRemoveSubscriber(): void
    {
        $subscriber = new Controller();

        $provider = (new ListenerProvider())->addSubscriber($subscriber);

        $this->assertInstanceOf(ListenerProvider::class, $provider->removeSubscriber($subscriber));
        $this->assertCount(0, $provider->getListenersForEvent(new SomethingHappened()));
        $this->assertCount(0, $provider->getListenersForEvent(new SomethingElseHappened()));
    }
}
