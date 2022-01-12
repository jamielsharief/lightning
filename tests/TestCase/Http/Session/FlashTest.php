<?php declare(strict_types=1);

namespace Lightning\Test\Http\Session;

use PHPUnit\Framework\TestCase;
use Lightning\Http\Session\Flash;
use Lightning\Http\Session\PhpSession;

final class FlashTest extends TestCase
{
    private PhpSession $session;
    private Flash $flash;

    public function setUp(): void
    {
        $this->session = new PhpSession();
        $this->session->start(null);

        $this->flash = new Flash($this->session);
    }

    public function tearDown(): void
    {
        $this->session->close();
    }

    public function testSet(): void
    {
        $this->assertInstanceOf(Flash::class, $this->flash->set('success', 'it worked!'));
    }

    public function testHas(): void
    {
        $this->flash->set('success', 'it worked!');

        $this->assertFalse($this->flash->has('error'));
        $this->assertTrue($this->flash->has('success'));
    }

    public function testGet(): void
    {
        $this->flash->set('success', 'it worked!');
        $this->assertEquals('it worked!', $this->flash->get('success'));
        $this->assertNull($this->flash->get('success'));
    }

    public function testGetMessages(): void
    {
        $this->assertEquals([], $this->flash->getMessages());

        $this->flash->set('a', 'b');

        $this->assertSame([
            'a' => 'b'
        ], $this->flash->getMessages());
    }

    public function testGetIterator(): void
    {
        $this->assertEquals([], iterator_to_array($this->flash));

        $this->flash->set('a', 'b');

        $this->assertSame([
            'a' => 'b'
        ], iterator_to_array($this->flash));
    }
}
