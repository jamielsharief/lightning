<?php declare(strict_types=1);

namespace Lightning\Test\Dotenv;

use RuntimeException;
use Lightning\Event\Event;
use PHPUnit\Framework\TestCase;

final class EventTest extends TestCase
{
    public function testType(): void
    {
        $event = new Event('Test.type', $this);
        $this->assertEquals('Test.type', $event->type());
    }

    public function testGetSource(): void
    {
        $event = new Event('Test.source');
        $this->assertNull($event->getSource());

        $event = new Event('Test.source', $this);
        $this->assertEquals($this, $event->getSource());
    }

    public function testCancellable(): void
    {
        $event = new Event('Test.cancellable', $this, [], true);
        $this->assertTrue($event->isCancelable());

        $event = new Event('Test.cancellable', $this, [], false);
        $this->assertFalse($event->isCancelable());
    }

    public function testStop(): void
    {
        $event = new Event('Test.stop', $this, [], true);
        $this->assertFalse($event->isPropagationStopped());

        $this->assertInstanceOf(Event::class, $event->stop());
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testStopException(): void
    {
        $event = new Event('Test.cancellable', $this, [], false);
        $this->assertFalse($event->isPropagationStopped());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This event cannot be stopped');
        $event->stop();
    }

    public function testGetData(): void
    {
        $event = new Event('Test.cancellable', $this, []);
        $this->assertEquals([], $event->getData());

        $event = new Event('Test.cancellable', $this, ['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $event->getData());
    }

    /**
     * @depends testGetData
     */
    public function testSetData(): void
    {
        $event = new Event('Test.cancellable', $this, []);
        $this->assertInstanceOf(Event::class, $event->setData(['foo' => 'bar']));
        $this->assertEquals(['foo' => 'bar'], $event->getData());
    }
}
