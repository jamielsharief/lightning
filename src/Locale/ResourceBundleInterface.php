<?php declare(strict_types=1);

namespace Lightning\Locale;

/**
 * Resource Bundle
 */
interface ResourceBundleInterface
{
    /**
     * Get an entry from the resource bundle
     *
     * @throws ResourceNotFoundException if the resource is not found
     */
    public function get(string $key): string;

    /**
     * Check if the resource bundle can return an entry for the key
     */
    public function has(string $key): bool;

    /**
     * Gets the Locale for this resource bundle
     */
    public function getLocale(): Locale;

    /**
     * Set the locale for the resource bundle
     */
    public function setLocale(Locale $locale): static;

    /**
     * Return an instance of resource bundle with a locale
     */
    public function withLocale(Locale $locale): static;

    /**
     * Sets the resource bundle name (domains in gettext)
     */
    public function setName(string $name): static;

    /**
     * Gets the resource bundle name
     */
    public function getName(): string;

    /**
     * Return an instance of the resource bundle with the name
     */
    public function withName(string $name): static;
}
