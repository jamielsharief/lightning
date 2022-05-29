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
     * Creates the Resource bundle
     *
     * @var string $bundle full path to directory which contains files
     */
    public static function create(Locale $locale, string $bundle): static;
}
