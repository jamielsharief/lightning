<?php declare(strict_types=1);

namespace Lightning\Test\Translator\MessageLoader;

use PHPUnit\Framework\TestCase;
use Lightning\Translator\MessageLoader\PhpMessageLoader;

final class PhpMessageLoaderTest extends TestCase
{
    public function testLoad(): void
    {
        $expected = [
            'Hello world!' => '¡Hola Mundo!',
            'You have %s messages' => 'Tienes %s mensaje(s)'
        ];
        $loader = new PhpMessageLoader(dirname(__DIR__). '/locale');
        $this->assertEquals($expected, $loader->load('application', 'es_ES'));
    }
}
