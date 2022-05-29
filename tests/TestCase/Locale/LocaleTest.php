<?php declare(strict_types=1);

namespace Lightning\Test\Locale;

use Lightning\Locale\Locale;
use PHPUnit\Framework\TestCase;

use Lightning\Locale\Exception\LocaleNotAvailableException;

final class LocaleTest extends TestCase
{
    public function testSetLocale(): void
    {
        $locale = new Locale('en_GB');

        $this->assertInstanceOf(Locale::class, $locale->set('en_US'));
    }

    public function testGetLocale(): void
    {
        $locale = new Locale('en_GB');
        $this->assertEquals('en_GB', $locale->get());
    }

    public function testGetDefaultLocale(): void
    {
        $locale = new Locale('en_GB', 'es_MX');
        $this->assertEquals('es_MX', $locale->getDefault());
    }

    public function testSetDefaultLocale(): void
    {
        $locale = new Locale('en_GB');
        $this->assertEquals('es_MX', $locale->setDefault('es_MX')->getDefault());
    }

    public function testToString(): void
    {
        $locale = new Locale('en_GB');
        $this->assertEquals('en_GB', $locale->toString());
    }

    public function testToStringMagic(): void
    {
        $locale = new Locale('en_GB');
        $this->assertEquals('en_GB', (string) $locale);
    }

    public function testSetAvailableLocales(): void
    {
        $locale = new Locale('en_GB');
        $this->assertInstanceOf(Locale::class, $locale->setAvailable(['en_GB','en_US','es_ES','es_MX']));
        $this->assertEquals(['en_GB','en_US','es_ES','es_MX'], $locale->getAvailable());
    }

    public function testGetAvailableLocalesEmpty(): void
    {
        $locale = new Locale('en_GB', 'en_US');
        $this->assertEquals([], $locale->getAvailable());
    }

    public function testSetNonAvailableLocale(): void
    {
        $locale = new Locale('en_GB', 'es_US', ['en_GB','en_US']);

        $this->expectException(LocaleNotAvailableException::class);
        $this->expectExceptionMessage('Locale `es_MX` is not available');

        $locale->set('es_MX');
    }

    public function testSetNonAvailableDefaultLocale(): void
    {
        $locale = new Locale('en_GB', 'es_US', ['en_GB','en_US']);

        $this->expectException(LocaleNotAvailableException::class);
        $this->expectExceptionMessage('Locale `es_MX` is not available');

        $locale->setDefault('es_MX');
    }

    public function testSetAvailableExceptionFromLocale(): void
    {
        $locale = new Locale('es_MX', 'en_US');

        $this->expectException(LocaleNotAvailableException::class);
        $this->expectExceptionMessage('Locale `es_MX` is not available');

        $locale->setAvailable(['en_GB','en_US']);
    }

    public function testSetAvailableExceptionFromDefault(): void
    {
        $locale = new Locale('en_US', 'es_MX');

        $this->expectException(LocaleNotAvailableException::class);
        $this->expectExceptionMessage('Locale `es_MX` is not available');

        $locale->setAvailable(['en_GB','en_US']);
    }

    public function testGetLanguage(): void
    {
        $locale = new Locale('es_ES');

        $this->assertEquals('es', $locale->getLanguage());
    }

    public function testGetDisplayLanguage(): void
    {
        $locale = new Locale('es_ES');

        $this->assertEquals('Spanish', $locale->getDisplayLanguage());
        $this->assertEquals('español', $locale->getDisplayLanguage('es_ES'));
    }

    public function testGetDisplayRegion(): void
    {
        $locale = new Locale('es_ES');

        $this->assertEquals('Spain', $locale->getDisplayRegion());
        $this->assertEquals('España', $locale->getDisplayRegion('es_ES'));
    }

    public function testGetDisplayName(): void
    {
        $locale = new Locale('es_ES');

        $this->assertEquals('Spanish (Spain)', $locale->getDisplayName());
        $this->assertEquals('español (España)', $locale->getDisplayName('es_ES'));
    }

    public function testGetAvailableLocalesFromConstructor(): void
    {
        $locale = new Locale('en_GB', 'en_US', ['en_GB','en_US','es_ES','es_MX']);
        $this->assertEquals(['en_GB','en_US','es_ES','es_MX'], $locale->getAvailable());
    }
}
