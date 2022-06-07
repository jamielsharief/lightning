<?php declare(strict_types=1);

namespace Lightning\Test\Translator;

use PHPUnit\Framework\TestCase;
use Lightning\Translator\Translator;
use Lightning\Translator\TranslatorInterface;
use Lightning\Translator\ResourceBundleFactory;

final class TranslatorTest extends TestCase
{
    private TranslatorInterface $translator;

    public function testTranslate(): void
    {
        $bundleFactory = new ResourceBundleFactory(__DIR__ . '/resources/test');
        $translator = new Translator($bundleFactory, 'en_US');

        $this->assertEquals('Hello world!', $translator->translate('Hello world!'));
    }

    public function testTranslateDoesNotExist(): void
    {
        $bundleFactory = new ResourceBundleFactory(__DIR__ . '/resources/test');
        $translator = new Translator($bundleFactory, 'en_US');

        $this->assertEquals('<-o->', $translator->translate('<-o->'));
    }

    public function testTranslateNull(): void
    {
        $bundleFactory = new ResourceBundleFactory(__DIR__ . '/resources/test');
        $translator = new Translator($bundleFactory, 'en_US');

        $this->assertEquals('', $translator->translate(null));
    }

    public function testTranslateLocale(): void
    {
        $bundleFactory = new ResourceBundleFactory(__DIR__ . '/resources/test');
        $translator = new Translator($bundleFactory, 'es_ES');

        $this->assertEquals('¡Hola Mundo!', $translator->translate('Hello world!'));
    }

    public function testTranslateWithChangeLocale(): void
    {
        $bundleFactory = new ResourceBundleFactory(__DIR__ . '/resources/test');
        $translator = new Translator($bundleFactory, 'es_ES');

        $translator->setLocale('en_GB');
        $this->assertEquals('Monday morning', $translator->translate('when'));
    }

    public function testTranslateSetLocale(): void
    {
        $bundleFactory = new ResourceBundleFactory(__DIR__ . '/resources/test');
        $translator = new Translator($bundleFactory, 'es_ES');

        $this->assertEquals('xx_XX', $translator->setLocale('xx_XX')->getLocale());
    }

    public function testTranslateWithLocale(): void
    {
        $bundleFactory = new ResourceBundleFactory(__DIR__ . '/resources/test');
        $translator = new Translator($bundleFactory, 'en_US');

        $translator2 = $translator->withLocale('es_ES');

        $this->assertInstanceOf(Translator::class, $translator2);
        $this->assertNotEquals($translator2, $translator);
        $this->assertEquals('es_ES', $translator2->getLocale());
    }

    public function testTranslateWithChangeLocaleDefaultFallback(): void
    {
        $bundleFactory = new ResourceBundleFactory(__DIR__ . '/resources/test');
        $translator = new Translator($bundleFactory, 'es_ES');

        $translator->setLocale('xx_XX');
        $this->assertEquals('¡Hola Mundo!', $translator->translate('Hello world!'));
    }

    public function testTranslateGetResourceBundleFactory(): void
    {
        $bundleFactory = new ResourceBundleFactory(__DIR__ . '/resources/test');
        $translator = new Translator($bundleFactory, 'es_ES');

        $this->assertEquals($bundleFactory, $translator->getResourceBundleFactory());
    }

    public function testTranslateWithPlaceholders(): void
    {
        $bundleFactory = new ResourceBundleFactory(__DIR__ . '/resources/test');
        $translator = new Translator($bundleFactory, 'es_ES');

        $this->assertEquals('Tienes 5 mensaje(s)', $translator->translate('You have {count} messages', ['count' => 5]));
    }

    public function testTranslateWithCustomPlural(): void
    {
        $bundleFactory = new ResourceBundleFactory(__DIR__ . '/resources/test');
        $translator = new Translator($bundleFactory, 'en_US');

        $message = 'You have zero apples|You have one apple|You have {count} apples';
        $this->assertEquals('You have zero apples', $translator->translate($message, ['count' => 0]));
        $this->assertEquals('You have one apple', $translator->translate($message, ['count' => 1]));
        $this->assertEquals('You have 2 apples', $translator->translate($message, ['count' => 2]));
        $this->assertEquals('You have 3 apples', $translator->translate($message, ['count' => 3]));
    }
}
