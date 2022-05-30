<?php declare(strict_types=1);
/**
 * LightningPHP
 * Copyright 2021 - 2022 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

namespace Lightning\Translator;

use Lightning\Locale\Locale;
use InvalidArgumentException;

interface TranslatorInterface
{
    /**
     * Sets the Locale
     */
    public function setLocale(string $locale): static;

    /**
     * Gets the locale
     */
    public function getLocale(): string;

    /**
     * Set Bundle
     */
    public function setResourceBundle(ResourceBundle $bundle): static;

    /**
     * Gets the Bundle
     */
    public function getResourceBundle(): ResourceBundle;

    /**
     * The translate method must always return a string
     *
     * @throws InvalidArgumentException default locale and bundle file does not exist
     */
    public function translate(?string $message, array $values = []): string;
}
