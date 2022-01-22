<?php declare(strict_types=1);

namespace Lightning\Test\Controller\Event;

use Lightning\Controller\Event\AfterFilterEvent;
use Psr\EventDispatcher\StoppableEventInterface;
use Lightning\Test\TestCase\Controller\Event\AbstractControllerEventTestCase;

final class AfterFilterEventTest extends AbstractControllerEventTestCase
{
    public function createEvent(): AfterFilterEvent
    {
        return new AfterFilterEvent($this->createController());
    }

    public function testNotStoppable(): void
    {
        $this->assertNotInstanceOf(StoppableEventInterface::class, $this->createEvent());
    }
}
