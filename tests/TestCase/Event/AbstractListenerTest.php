<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\Event;

use PHPUnit\Framework\TestCase;
use Lightning\Event\AbstractListener;
use Lightning\Event\Exception\EventException;

class BeforeGoToBed
{
}

class SleepListener extends AbstractListener
{
    public function handle(BeforeGoToBed $event): void
    {
        throw new EventException('Coffee');
    }
}

final class AbstractListenerTest extends TestCase
{
    public function testInvokable(): void
    {
        $listener = new SleepListener();
        $this->expectException(EventException::class);
        $listener->__invoke((new BeforeGoToBed()));
    }
}
