<?php declare(strict_types=1);

namespace Lightning\Test\Http\Session;

use Redis;
use LogicException;
use PHPUnit\Framework\TestCase;
use function Lightning\Dotenv\env;

use Lightning\Http\Session\PhpSession;

use Lightning\Http\Session\RedisSession;
use Lightning\Http\Session\SessionInterface;

final class SessionTest extends TestCase
{
    private string $sessionId;

    public function setUp(): void
    {
        $this->sessionId = uniqid();
    }

    public function sessionProvider()
    {
        $redis = new Redis();
        $host = env('REDIS_HOST') ?: '127.0.0.1';
        $port = env('REDIS_PORT') ?: 6379;
        $redis->pconnect($host, (int) $port);

        return [
            [new PhpSession()],
            [new RedisSession($redis)],
        ];
    }

    /**
     * @dataProvider sessionProvider
     */
    public function testStart(SessionInterface $session): void
    {
        $this->assertFalse($session->isStarted());
        $this->assertTrue($session->start($this->sessionId));
        $this->assertTrue($session->isStarted());
        $this->assertFalse($session->start($this->sessionId));
        $session->close();
    }

    /**
    * @dataProvider sessionProvider
    */
    public function testMustBeStarted(SessionInterface $session): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Session must be started before it can be used');

        $session->get('foo');
    }

    /**
     * @dataProvider sessionProvider
     */
    public function testClose(SessionInterface $session): void
    {
        $session->start($this->sessionId);

        $this->assertTrue($session->close());

        $this->assertFalse($session->isStarted());
        $this->assertFalse($session->close());
    }

    /**
     * @dataProvider sessionProvider
     */
    public function testSet(SessionInterface $session): void
    {
        $session->start($this->sessionId);
        $session->set('foo', 'bar');
        $this->assertEquals('bar', $session->get('foo'));

        $session->close();
    }

    /**
     * @dataProvider sessionProvider
     */
    public function testGet(SessionInterface $session): void
    {
        $session->start($this->sessionId);
        $session->set('foo', 'bar');
        $this->assertSame('bar', $session->get('foo'));

        $session->close();
    }

    /**
     * @dataProvider sessionProvider
     */
    public function testGetNoResult(SessionInterface $session): void
    {
        $session->start($this->sessionId);

        $this->assertNull($session->get('foo'));

        $session->close();
    }

    /**
     * @dataProvider sessionProvider
     */
    public function testGetUsingDefault(SessionInterface $session): void
    {
        $session->start($this->sessionId);
        $this->assertSame('bar', $session->get('foo', 'bar'));

        $session->close();
    }

    /**
     * @dataProvider sessionProvider
     */
    public function testHas(SessionInterface $session): void
    {
        $session->start($this->sessionId);

        $this->assertFalse($session->has('foo'));

        $session->set('foo', 'bar');

        $this->assertTrue($session->has('foo'));

        $session->close();
    }

    /**
     * @dataProvider sessionProvider
     */
    public function testUnset(SessionInterface $session): void
    {
        $session->start($this->sessionId);
        $session->set('foo', 'bar');
        $this->assertTrue($session->has('foo'));

        $session->unset('foo');
        $this->assertFalse($session->has('bar'));

        $session->close();
    }

    /**
     * @dataProvider sessionProvider
     */
    public function testClear(SessionInterface $session): void
    {
        $session->start($this->sessionId);
        $session->set('foo', 'bar');
        $this->assertTrue($session->has('foo'));

        $session->clear();

        $this->assertFalse($session->has('bar'));

        $session->close();
    }

    /**
     * @dataProvider sessionProvider
     */
    public function testDestroy(SessionInterface $session): void
    {
        $session->start('12345');
        $session->set('foo', 'bar');
        $session->close();

        $session->start('12345');
        $this->assertTrue($session->has('foo'));
        $session->destroy();
        $this->assertFalse($session->isStarted());

        $session->close();

        $session->start('12345');
        $this->assertFalse($session->has('bar'));
        $session->close();
    }

    /**
     * @dataProvider sessionProvider
     */
    public function testRegenerateId(SessionInterface $session): void
    {
        $id = bin2hex(random_bytes(16));

        $session->start($id);
        $session->set('foo', 'bar');

        $this->assertTrue($session->regenerateId());

        $session->close();
        $id = $session->getId();

        $this->assertTrue($session->start($id));
        $this->assertTrue($session->has('foo'));

        $session->close();
    }
}
