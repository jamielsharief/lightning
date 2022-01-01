<?php declare(strict_types=1);
/**
 * LightningPHP
 * Copyright 2021 Jamiel Sharief.
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
    * Gets the locale
    *
    * @return string
    */
    public function getLocale(): string;

    /**
     * Sets the Locale
     *
     * @param string $locale
     * @return self|void
     */
    public function setLocale(string $locale);

    /**
     * Set Domain
     *
     * @param string $domain
     * @return self|void
     */
    public function setDomain(string $domain);

    /**
     * Gets the Domaing
     *
     * @return string
     */
    public function getDomain(): string;

    /**
     * The translate method must always return a string
     *
     * @param string|null $message
     * @param array $values
     * @throws InvalidArgumentException default locale and domain file does not exist
     * @return string
     */
    public function translate(?string $message, array $values = []): string;
}
