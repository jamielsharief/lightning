<?php declare(strict_types=1);

namespace Lightning\Test\TestSuite;

use PHPUnit\Framework\TestCase;
use Lightning\TestSuite\TestSession;

final class TestSessionTest extends TestCase
{
    public function testSet(): void
    {
        $session = new TestSession();
        $session->set('foo', 'bar');
        $this->assertArrayHasKey('foo', $_SESSION);
    }

    public function testGet(): void
    {
        $session = new TestSession();
        $this->assertNull($session->get('foo'));

        $_SESSION['foo'] = 'bar';
        $this->assertEquals('bar', $session->get('foo'));
    }

    public function testHas(): void
    {
        $session = new TestSession();
        $this->assertFalse($session->has('foo'));
        $_SESSION['foo'] = 'bar';
        $this->assertTrue($session->has('foo'));
    }

    public function testClear(): void
    {
        $session = new TestSession();
        $_SESSION['foo'] = 'bar';

        $session->clear();
        $this->assertFalse($session->has('foo'));
    }
}
