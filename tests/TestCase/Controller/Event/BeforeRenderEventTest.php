<?php declare(strict_types=1);

namespace Lightning\Test\Controller\Event;

use Psr\EventDispatcher\StoppableEventInterface;
use Lightning\Controller\Event\BeforeRenderEvent;
use Lightning\Test\TestCase\Controller\Event\AbstractControllerEventTestCase;

final class BeforeRenderEventTest extends AbstractControllerEventTestCase
{
    public function createEvent(): BeforeRenderEvent
    {
        return new BeforeRenderEvent($this->createController());
    }

    public function testStop(): void
    {
        $event = $this->createEvent();
        $this->assertInstanceOf(StoppableEventInterface::class, $event);
        $this->assertFalse($event->isPropagationStopped());
        $event->stop();
        $this->assertTrue($event->isPropagationStopped());
    }
}
