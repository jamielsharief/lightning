<?php declare(strict_types=1);

namespace Lightning\Test\Translator;

use RuntimeException;
use PHPUnit\Framework\TestCase;
use Lightning\Translator\Translator;
use Lightning\Translator\TranslatorManager;
use Lightning\Translator\ResourceBundleFactory;

final class TranslatorManagerTest extends TestCase
{
    public function testGet(): void
    {
        $bundleFactory = new ResourceBundleFactory(__DIR__ . '/resources/test');
        $translator = new Translator($bundleFactory, 'en_US');

        TranslatorManager::set($translator);
        $this->assertEquals($translator, TranslatorManager::get());

        TranslatorManager::unset();
    }

    public function testGetException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Translator object not set');
        TranslatorManager::get();
    }
}
