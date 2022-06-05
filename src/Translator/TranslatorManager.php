<?php declare(strict_types=1);

namespace Lightning\Translator;

use RuntimeException;

/**
 * Global Translator Manager which will be used by the __ function
 */
class TranslatorManager
{
    private static ?TranslatorInterface $translator = null;

    /**
     * Sets the Translator object
     */
    public static function set(TranslatorInterface $translator): void
    {
        static::$translator = $translator;
    }

    /**
     * Gets the Translator object
     * @throws RuntimeException if translator not set
     */
    public static function get(): TranslatorInterface
    {
        if (! isset(static::$translator)) {
            throw new RuntimeException('Translator object not set');
        }

        return static::$translator;
    }

    /**
     * Unsets the TranslatorManager
     */
    public static function unset(): void
    {
        static::$translator = null;
    }
}
