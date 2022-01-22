<?php declare(strict_types=1);

namespace Lightning\Test\Controller\Event;

use Psr\EventDispatcher\StoppableEventInterface;
use Lightning\Controller\Event\AfterInitializeEvent;
use Lightning\Test\TestCase\Controller\Event\AbstractControllerEventTestCase;

final class AfterInitializeEventTest extends AbstractControllerEventTestCase
{
    public function createEvent(): AfterInitializeEvent
    {
        return new AfterInitializeEvent($this->createController());
    }

    public function testNotStoppable(): void
    {
        $this->assertNotInstanceOf(StoppableEventInterface::class, $this->createEvent());
    }
}
