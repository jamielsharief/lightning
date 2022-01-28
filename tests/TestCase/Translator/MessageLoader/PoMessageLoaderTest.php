<?php declare(strict_types=1);

namespace Lightning\Test\Translator\MessageLoader;

use PHPUnit\Framework\TestCase;
use Lightning\Translator\MessageLoader\PoMessageLoader;

final class PoMessageLoaderTest extends TestCase
{
    private string $tempPath;
    private PoMessageLoader $loader;

    public function setUp(): void
    {
        $this->tempPath = sys_get_temp_dir() . '/' . uniqid();
        mkdir($this->tempPath, 0777, true);

        $this->loader = new PoMessageLoader(dirname(__DIR__). '/locale', $this->tempPath);
    }

    public function testLoad(): void
    {
        $expected = [
            '' => 'Project-Id-Version: Demo 1.0.0\nReport-Msgid-Bugs-To: user@example.com \nLast-Translator:  <user@example.com>\nLanguage: es\nMIME-Version: 1.0\nContent-Type: text/plain; charset=UTF-8\nContent-Transfer-Encoding: 8bit\nPlural-Forms: nplurals=2; plural=(n != 1);\n',
            'This is a translation test.' => 'Esta es una prueba de traducción.',
            'Welcome to our application %s.' => 'Bienvenido a nuestra aplicación %s',
            'Welcome back %1$s, your last vist was on %2$s.' => 'Bienvenido de nuevo %1$s, tu última visita fue en %2$s.',
            'This is an example of a really long line of text, that will be translated into another language.' => 'Este es un ejemplo de una línea de texto realmente larga, que será traducida a otro idioma.'
        ];

        $this->assertEquals($expected, $this->loader->load('test', 'es_ES'));
        $this->assertFileExists($this->tempPath . '/0cd5f955ec6bdefe873857ff44352f2a.cached');
        $this->assertSame('1473906845a9e7ddcccdcbe3f2e9decfe7ae77b448350f868e6df71e86248ec9', hash_file('sha256', '/tmp/61f4129b1f65b/0cd5f955ec6bdefe873857ff44352f2a.cached'));
    }
}
