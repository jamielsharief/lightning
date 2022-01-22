<?php declare(strict_types=1);

namespace Lightning\Test\Controller\Event;

use Lightning\Controller\Event\AfterRenderEvent;
use Psr\EventDispatcher\StoppableEventInterface;
use Lightning\Test\TestCase\Controller\Event\AbstractControllerEventTestCase;

final class AfterRenderEventTest extends AbstractControllerEventTestCase
{
    public function createEvent(): AfterRenderEvent
    {
        return new AfterRenderEvent($this->createController());
    }

    public function testNotStoppable(): void
    {
        $this->assertNotInstanceOf(StoppableEventInterface::class, $this->createEvent());
    }
}
