<?php declare(strict_types=1);

namespace Lightning\Test\TestSuite;

use PHPUnit\Framework\TestCase;
use Lightning\TestSuite\TestEventDispatcher;

class FooBar
{
}

final class TestEventDispatcherTest extends TestCase
{
    public function testGetDispatchedEvents(): void
    {
        $eventDispatcher = new TestEventDispatcher();
        $this->assertEquals([], $eventDispatcher->getDispatchedEventClasses());
    }
}
