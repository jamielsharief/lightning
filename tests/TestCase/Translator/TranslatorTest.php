<?php declare(strict_types=1);

namespace Lightning\Test\Translator;

use PHPUnit\Framework\TestCase;
use Lightning\Translator\Translator;
use Lightning\Translator\TranslatorInterface;
use Lightning\Translator\MessageLoader\PhpMessageLoader;

final class TranslatorTest extends TestCase
{
    private TranslatorInterface $translator;

    public function testTranslate(): void
    {
        $translator = new Translator(new PhpMessageLoader(__DIR__ . '/locale'), 'en_US');

        $this->assertEquals('Hello world!', $translator->translate('Hello world!'));
    }

    public function testTranslateLocale(): void
    {
        $translator = new Translator(new PhpMessageLoader(__DIR__ . '/locale'), 'es_ES');

        $this->assertEquals('¡Hola Mundo!', $translator->translate('Hello world!'));
    }

    public function testTranslateLocaleFallbackPrimaryLanguage(): void
    {
        $translator = new Translator(new PhpMessageLoader(__DIR__ . '/locale'), 'es_MX');
        $this->assertEquals('Es domingo por la noche.', $translator->translate('It is sunday night.'));
    }

    public function testTranslateLocaleFallbackDefaultLocale(): void
    {
        $translator = new Translator(new PhpMessageLoader(__DIR__ . '/locale'), 'en_GB');

        $translator->setLocale('jp_JP');
        $this->assertEquals('Monday morning', $translator->translate('when'));
    }

    public function testTranslateWithChangeLocale(): void
    {
        $translator = new Translator(new PhpMessageLoader(__DIR__ . '/locale'), 'es_ES');

        $translator->setLocale('en_GB');
        $this->assertEquals('Monday morning', $translator->translate('when'));
    }

    public function testTranslateWithChangeDomain(): void
    {
        $translator = new Translator(new PhpMessageLoader(__DIR__ . '/locale'), 'es_ES');

        $translator->setDomain('domain');
        $this->assertEquals('Es un hermoso dia.', $translator->translate('It is a beautiful day.'));
    }

    public function testTranslateWithPlaceholders(): void
    {
        $translator = new Translator(new PhpMessageLoader(__DIR__ . '/locale'), 'es_ES');

        $this->assertEquals('Tienes 5 mensaje(s)', $translator->translate('You have {count} messages', ['count' => 5]));
    }

    public function testTranslateWithCustomPlural(): void
    {
        $translator = new Translator(new PhpMessageLoader(__DIR__ . '/locale'), 'en_US');
        $message = 'You have zero apples|You have one apple|You have {count} apples';
        $this->assertEquals('You have zero apples', $translator->translate($message, ['count' => 0]));
        $this->assertEquals('You have one apple', $translator->translate($message, ['count' => 1]));
        $this->assertEquals('You have 2 apples', $translator->translate($message, ['count' => 2]));
        $this->assertEquals('You have 3 apples', $translator->translate($message, ['count' => 3]));
    }
}
