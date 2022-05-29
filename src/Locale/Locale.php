<?php declare(strict_types=1);

namespace Lightning\Locale;

use Stringable;
use Locale as GlobalLocale;
use Lightning\Locale\Exception\LocaleNotAvailableException;

/**
 * Locale
 */
class Locale implements Stringable
{
    /**
     * @param string $locale BCP 47 tag ,e.g en_GB
     * @param string $defaultLocale BCP 47 tag ,e.g en_GB
     * @param array<string> [en_GB]
     */
    public function __construct(private string $locale, private string $defaultLocale = 'en_US', private array $availableLocales = [])
    {
    }

    /**
     * Get the locale
     */
    public function get(): string
    {
        return $this->locale;
    }

    /**
     * Set the locale
     */
    public function set(string $locale): static
    {
        $this->checkLocale($locale);

        $this->locale = $locale;

        return $this;
    }

    /**
     * Get the value of default lcoale
     */
    public function getDefault(): string
    {
        return $this->defaultLocale;
    }

    /**
     * Set the default locale
     */
    public function setDefault(string $locale): static
    {
        $this->checkLocale($locale);

        $this->defaultLocale = $locale;

        return $this;
    }

    /**
     * Gets the available locales
     */
    public function getAvailable(): array
    {
        return $this->availableLocales;
    }

    /**
     * Set the available locales, an array of tags ['en_US']
     */
    public function setAvailable(array $locales): static
    {
        $this->availableLocales = $locales;

        $this->checkLocale($this->locale);
        $this->checkLocale($this->defaultLocale);

        return $this;
    }

    /**
     * Checks if a locale is allowed
     */
    public function isAvailable(string $locale): bool
    {
        return empty($this->availableLocales) || in_array($locale, $this->availableLocales);
    }

    /**
     * Checks that locale is available
     *
     * @throws LocaleNotAvailableException
     */
    private function checkLocale(string $locale): void
    {
        if (! $this->isAvailable($locale)) {
            throw new LocaleNotAvailableException(sprintf('Locale `%s` is not available', $locale));
        }
    }

    /**
     * Gets the language for this locale
     */
    public function getLanguage(): string
    {
        return GlobalLocale::getPrimaryLanguage($this->locale);
    }

    /**
     * Gets the display language for this locale
     */
    public function getDisplayLanguage(?string $displayLocale = null): ?string
    {
        return GlobalLocale::getDisplayLanguage($this->locale, $displayLocale) ?: null;
    }

    /**
     * Gets the display region (e.g Italy) for this locale
     */
    public function getDisplayName(?string $displayLocale = null): ?string
    {
        return GlobalLocale::getDisplayName($this->locale, $displayLocale) ?: null;
    }

    /**
     * Gets the display region (e.g Italy) for this locale
     */
    public function getDisplayRegion(?string $displayLocale = null): ?string
    {
        return GlobalLocale::getDisplayRegion($this->locale, $displayLocale) ?: null;
    }

    /**
     * Gets this Locale as a string
     */
    public function toString(): string
    {
        return $this->locale;
    }

    public function __toString(): string
    {
        return $this->locale;
    }
}
