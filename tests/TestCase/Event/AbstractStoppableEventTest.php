<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\Event;

use PHPUnit\Framework\TestCase;
use Lightning\Event\AbstractStoppableEvent;
use Psr\EventDispatcher\StoppableEventInterface;

class SomeKindOfEvent extends AbstractStoppableEvent
{
}

class DucksController
{
}

final class AbstractStoppableEventTest extends TestCase
{
    public function testImplements(): void
    {
        $this->assertInstanceOf(StoppableEventInterface::class, new SomeKindOfEvent(new DucksController()));
    }

    public function testGetSource(): void
    {
        $controller = new DucksController();
        $event = new SomeKindOfEvent($controller);

        $this->assertEquals($controller, $event->getSource());
    }

    public function testGetTimestamp(): void
    {
        $timestamp = time();
        $event = new SomeKindOfEvent(new DucksController());

        $this->assertEquals($timestamp, $event->getTimestamp());
    }

    public function testToString(): void
    {
        $event = new SomeKindOfEvent(new DucksController());
        $this->assertEquals($event, unserialize($event->toString()));
    }

    public function testIsPropgationStopped(): void
    {
        $event = new SomeKindOfEvent(new DucksController());
        $this->assertFalse($event->isPropagationStopped());
        $event->stopPropagation();
        $this->assertTrue($event->isPropagationStopped());
    }
}
