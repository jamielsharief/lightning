<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\Event;

use PHPUnit\Framework\TestCase;
use Lightning\Event\AbstractEvent;

class SampleEvent extends AbstractEvent
{
}

class MembersController
{
}

final class AbstractEventTest extends TestCase
{
    public function testGetSource(): void
    {
        $controller = new MembersController();
        $event = new SampleEvent($controller);

        $this->assertEquals($controller, $event->getSource());
    }

    public function testGetTimestamp(): void
    {
        $timestamp = time();
        $event = new SampleEvent(new MembersController());

        $this->assertEquals($timestamp, $event->getTimestamp());
    }

    public function testToString(): void
    {
        $event = new SampleEvent(new MembersController());
        $this->assertEquals($event, unserialize($event->toString()));
    }
}
