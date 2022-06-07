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

interface TranslatorInterface
{
    /**
     * Sets the locale for the translator
     */
    public function setLocale(string $locale): static;

    /**
     * Gets the locale for the translator
     */
    public function getLocale(): string;

    /**
     * Gets an instance of the translator with a different locale
     */
    public function withLocale(string $locale): static;

    /**
     * The translate method MUST always return a string
     */
    public function translate(?string $message, array $values = []): string;
}
