<?php declare(strict_types=1);

namespace Lightning\Test\Translator\MessageLoader;

use PHPUnit\Framework\TestCase;
use Lightning\Cache\MemoryCache;
use Lightning\Translator\MessageLoader\PoMessageLoader;

final class PoMessageLoaderTest extends TestCase
{
    public function testLoad(): void
    {
        $expected = [
            '' => 'Project-Id-Version: Demo 1.0.0\nReport-Msgid-Bugs-To: user@example.com \nLast-Translator:  <user@example.com>\nLanguage: es\nMIME-Version: 1.0\nContent-Type: text/plain; charset=UTF-8\nContent-Transfer-Encoding: 8bit\nPlural-Forms: nplurals=2; plural=(n != 1);\n',
            'This is a translation test.' => 'Esta es una prueba de traducción.',
            'Welcome to our application %s.' => 'Bienvenido a nuestra aplicación %s',
            'Welcome back %1$s, your last vist was on %2$s.' => 'Bienvenido de nuevo %1$s, tu última visita fue en %2$s.'
        ];

        $loader = new PoMessageLoader(dirname(__DIR__). '/locale');
        $this->assertEquals($expected, $loader->load('test', 'es_ES'));
    }

    /**
     * Check that caching is working
     *
     * @return void
     */
    public function testLoadCached(): void
    {
        $path = dirname(__DIR__). '/locale/test.es_ES.po';
        $key = md5($path);

        $cache = new MemoryCache();
        $loader = new PoMessageLoader(dirname(__DIR__). '/locale', $cache);
        $loader->load('test', 'es_ES');

        $this->assertTrue($cache->has($key));

        $cache->set($key, ['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $cache->get($key));
    }
}
