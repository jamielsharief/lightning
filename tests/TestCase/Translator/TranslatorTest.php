<?php declare(strict_types=1);

namespace Lightning\Test\Translator;

use PHPUnit\Framework\TestCase;
use Lightning\Translator\Translator;
use Lightning\Translator\ResourceBundle;
use Lightning\Translator\TranslatorInterface;

final class TranslatorTest extends TestCase
{
    private TranslatorInterface $translator;

    public function testTranslate(): void
    {
        $bundle = ResourceBundle::create('en_US', __DIR__ . '/resources/test');
        $translator = new Translator($bundle);

        $this->assertEquals('Hello world!', $translator->translate('Hello world!'));
    }

    public function testTranslateDoesNotExist(): void
    {
        $bundle = ResourceBundle::create('en_US', __DIR__ . '/resources/test');
        $translator = new Translator($bundle);

        $this->assertEquals('<-o->', $translator->translate('<-o->'));
    }

    public function testTranslateNull(): void
    {
        $bundle = ResourceBundle::create('en_US', __DIR__ . '/resources/test');
        $translator = new Translator($bundle);

        $this->assertEquals('', $translator->translate(null));
    }

    public function testTranslateLocale(): void
    {
        $bundle = ResourceBundle::create('es_ES', __DIR__ . '/resources/test');
        $translator = new Translator($bundle);

        $this->assertEquals('¡Hola Mundo!', $translator->translate('Hello world!'));
    }

    public function testTranslateWithChangeLocale(): void
    {
        $bundle = ResourceBundle::create('es_ES', __DIR__ . '/resources/test');
        $translator = new Translator($bundle);

        $translator->setLocale('en_GB');
        $this->assertEquals('Monday morning', $translator->translate('when'));
    }

    public function testTranslateSetLocale(): void
    {
        $bundle = ResourceBundle::create('es_ES', __DIR__ . '/resources/test');
        $translator = new Translator($bundle);

        $this->assertEquals('xx_XX', $translator->setLocale('xx_XX')->getLocale());
    }

    public function testTranslateWithChangeLocaleDefaultFallback(): void
    {
        $bundle = ResourceBundle::create('es_ES', __DIR__ . '/resources/test');
        $translator = new Translator($bundle);

        $translator->setLocale('xx_XX');
        $this->assertEquals('¡Hola Mundo!', $translator->translate('Hello world!'));
    }

    /**
     * @internal This should also be in a different locale, so can test messages are loaded.
     */
    public function testTranslateChangeResourceBundle(): void
    {
        $bundle = ResourceBundle::create('es_ES', __DIR__ . '/resources/test');
        $translator = new Translator($bundle);

        $bundle = ResourceBundle::create('en_US', __DIR__ . '/resources/app'); //

        $translator->setResourceBundle($bundle);
        $this->assertEquals('Hola mundo', $translator->translate('hello_world'));
    }

    public function testTranslateGetBundle(): void
    {
        $bundle = ResourceBundle::create('es_ES', __DIR__ . '/resources/test');
        $translator = new Translator($bundle);

        $this->assertEquals($bundle, $translator->getResourceBundle());
    }

    public function testTranslateWithPlaceholders(): void
    {
        $bundle = ResourceBundle::create('es_ES', __DIR__ . '/resources/test');
        $translator = new Translator($bundle);

        $this->assertEquals('Tienes 5 mensaje(s)', $translator->translate('You have {count} messages', ['count' => 5]));
    }

    public function testTranslateWithCustomPlural(): void
    {
        $bundle = ResourceBundle::create('en_US', __DIR__ . '/resources/test');
        $translator = new Translator($bundle);

        $message = 'You have zero apples|You have one apple|You have {count} apples';
        $this->assertEquals('You have zero apples', $translator->translate($message, ['count' => 0]));
        $this->assertEquals('You have one apple', $translator->translate($message, ['count' => 1]));
        $this->assertEquals('You have 2 apples', $translator->translate($message, ['count' => 2]));
        $this->assertEquals('You have 3 apples', $translator->translate($message, ['count' => 3]));
    }
}
