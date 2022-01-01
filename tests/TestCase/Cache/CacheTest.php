<?php declare(strict_types=1);

namespace Lightning\Test\Cache;

use DateInterval;
use Lightning\Cache\ApcuCache;
use Lightning\Cache\FileCache;
use Lightning\Cache\RedisCache;
use PHPUnit\Framework\TestCase;
use Lightning\Cache\MemoryCache;
use function Lightning\Dotenv\env;
use Lightning\Cache\AbstractCache;
use Psr\SimpleCache\CacheInterface;

use Lightning\Cache\Exception\InvalidArgumentException;

/**
 * TODO: test ttl
 */
final class CacheTest extends TestCase
{
    public function cacheProvider()
    {
        $prefix = uniqid();
        $path = sys_get_temp_dir() . '/' . uniqid();
        $redis = new \Redis();
        $host = env('REDIS_HOST') ?: '127.0.0.1';
        $port = env('REDIS_PORT') ?: 6379;
        $redis->pconnect($host, (int) $port);

        return [
            [new MemoryCache()],
            [new FileCache($path, $prefix)],
            [new ApcuCache($prefix)],
            [new RedisCache($redis, $prefix)]
        ];
    }

    /**
     * @internal constructors are not showing as called in code coverage, I am wondering if this has something
     * todo with dataprovider, so this test is me checking if i can get coverage.
     */
    public function testCacheInterface(): void
    {
        $prefix = uniqid();
        $path = sys_get_temp_dir() . '/' . uniqid();
        $redis = new \Redis();
        $host = env('REDIS_HOST') ?: '127.0.0.1';
        $port = env('REDIS_PORT') ?: 6379;
        $redis->pconnect($host, (int) $port);
        $this->assertInstanceOf(CacheInterface::class, new MemoryCache());
        $this->assertInstanceOf(CacheInterface::class, new FileCache($path, $prefix));
        $this->assertInstanceOf(CacheInterface::class, new ApcuCache($prefix));
        $this->assertInstanceOf(CacheInterface::class, new RedisCache($redis, $prefix));
    }

    /**
     * @dataProvider cacheProvider
     */
    public function testSet(AbstractCache $cache)
    {
        $this->assertTrue($cache->set('testSet', 'true'));
    }

    /**
     * @depends testSet
     * @dataProvider cacheProvider
     */
    public function testGet(AbstractCache $cache)
    {
        $cache->set('testGet', 'true');
        $this->assertEquals('true', $cache->get('testGet'));
        $this->assertNull($cache->get('testGetNotFound'));
        $this->assertEquals('result', $cache->get('testGetWithDefault', 'result'));
    }

    /**
    * @dataProvider cacheProvider
    */
    public function testHas(AbstractCache $cache)
    {
        $cache->set('testHas', 'true');

        $this->assertTrue($cache->has('testHas'));
        $this->assertFalse($cache->has('testHasNot'));
    }

    /**
     * @depends testSet
     * @dataProvider cacheProvider
     */
    public function testDelete(AbstractCache $cache)
    {
        $cache->set('testDelete', 'true');

        $this->assertTrue($cache->delete('testDelete'));
        $this->assertFalse($cache->delete('testDelete'));
    }

    /**
     * @depends testSet
     * @dataProvider cacheProvider
     */
    public function testIncrement(AbstractCache $cache)
    {
        if ($cache instanceof FileCache) {
            $this->markTestSkipped('File cache does not support increment');
        }
        $this->assertEquals(1, $cache->increment('testIncrement'));
        $this->assertEquals(3, $cache->increment('testIncrement', 2));
        $this->assertEquals(4, $cache->increment('testIncrement', 1, 1));
    }

    /**
     * @depends testSet
     * @dataProvider cacheProvider
     */
    public function testDecrement(AbstractCache $cache)
    {
        if ($cache instanceof FileCache) {
            $this->markTestSkipped('File cache does not support decrement');
        }
        $cache->set('testDecrement', 5);
        $this->assertEquals(4, $cache->decrement('testDecrement'));
        $this->assertEquals(2, $cache->decrement('testDecrement', 2));
        $this->assertEquals(1, $cache->decrement('testDecrement', 1, 1));
    }

    /**
     * @depends testSet
     * @dataProvider cacheProvider
     */
    public function testSetMultiple(AbstractCache $cache)
    {
        $data = [
            'testSetMultiple1' => 1,
            'testSetMultiple2' => 2
        ];
        $this->assertTrue($cache->setMultiple($data));
    }

    /**
     * @depends testSet
     * @dataProvider cacheProvider
     */
    public function testGetMultiple(AbstractCache $cache)
    {
        $data = [
            'testGetMultiple1' => 1,
            'testGetMultiple2' => 2
        ];
        $cache->setMultiple($data);

        $this->assertEquals($data, $cache->getMultiple(['testGetMultiple1','testGetMultiple2']));
    }

    /**
     * @depends testSet
     * @dataProvider cacheProvider
     */
    public function testDeleteMultiple(AbstractCache $cache)
    {
        $data = [
            'testDeleteMultiple1' => 1,
            'testDeleteMultiple2' => 2,
            'testDeleteMultiple3' => 2
        ];
        $cache->setMultiple($data);

        $this->assertTrue($cache->deleteMultiple(['testDeleteMultiple1','testDeleteMultiple2']));
        $this->assertFalse($cache->deleteMultiple(['testDeleteMultiple3','testDeleteMultipleDoesNotExist']));
        $this->assertFalse($cache->deleteMultiple(['testDeleteMultiple1','testDeleteMultiple2']));
    }

    /**
     * @depends testSet
     * @dataProvider cacheProvider
     */
    public function testClear(AbstractCache $cache)
    {
        $cache->set('testClear', 'true');
        $cache->clear();
        $this->assertFalse($cache->has('testClear'));
    }
    /**
     * @depends testSet
     * @dataProvider cacheProvider
     */
    public function testSetInvalidKey(AbstractCache $cache)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid key `#1234`');

        $cache->set('#1234', 'foo');
    }
    /**
     * @depends testSet
     * @dataProvider cacheProvider
     */
    public function testHasInvalidKey(AbstractCache $cache)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid key `#1234`');

        $cache->has('#1234');
    }
    /**
     * @depends testSet
     * @dataProvider cacheProvider
     */
    public function testGetInvalidKey(AbstractCache $cache)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid key `#1234`');

        $cache->get('#1234');
    }
    /**
     * @depends testSet
     * @dataProvider cacheProvider
     */
    public function testDeleteInvalidKey(AbstractCache $cache)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid key `#1234`');

        $cache->get('#1234');
    }

    /**
     * @depends testSet
     * @dataProvider cacheProvider
     */
    public function testSetWithZeroDuration(AbstractCache $cache)
    {
        $this->assertTrue($cache->set('testSetWithZeroDuration', 'bar', 0));
    }
    /**
     * @depends testSet
     * @dataProvider cacheProvider
     */
    public function testSetWithFiveSecondsDuration(AbstractCache $cache)
    {
        $this->assertTrue($cache->set('testSetWithFiveSecondsDuration', 'bar', 5));
    }
    /**
     * @depends testSet
     * @dataProvider cacheProvider
     */
    public function testSetWithDateInterval(AbstractCache $cache)
    {
        $this->assertTrue($cache->set('testSetWithDateInterval', 'bar', new DateInterval('PT5M')));
    }

    /**
     * @depends testSet
     * @dataProvider cacheProvider
     */
    public function testSetWithInvalidDuration(AbstractCache $cache)
    {
        if ($cache instanceof MemoryCache) {
            $this->markTestSkipped('Memory cache does not support duration');
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid ttl value must be an int, null or a DateInterval instance');

        $cache->set('testSetWithInvalidDuration', 'bar', 'now');
    }

    /**
     * @xdepends testSet
     * @xdepends testHas
     * @xdepends testGet
     * @dataProvider cacheProvider
     */
    public function testSetExpiring(AbstractCache $cache)
    {
        if ($cache instanceof MemoryCache) {
            $this->markTestSkipped('Memory cache does not support duration');
        }

        $cache->set('testSetExpiring', 'value', 1);
        $this->assertTrue($cache->has('testSetExpiring'));
        $this->assertEquals('value', $cache->get('testSetExpiring'));

        if ($cache instanceof ApcuCache) {
            $this->markTestSkipped('Apcu cache is only cleared on next request so it can be tested');
        }

        sleep(2);
        $this->assertFalse($cache->has('testSetExpiring'));
        $this->assertNull($cache->get('testSetExpiring'));
    }
}
