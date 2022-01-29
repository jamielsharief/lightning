<?php declare(strict_types=1);

namespace Lightning\Test\Translator\MessageLoader;

use PHPUnit\Framework\TestCase;
use Lightning\Translator\Exception\MessageFileNotFound;
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
    }

    public function testLoadException(): void
    {
        $this->expectException(MessageFileNotFound::class);
        $this->expectExceptionMessage('Message file `foo.es_ES.po` not found');
        $this->loader->load('foo', 'es_ES');
    }
}
