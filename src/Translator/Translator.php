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

use Locale;
use MessageFormatter;
use RuntimeException;
use Lightning\Translator\Exception\MessageFileNotFound;

class Translator implements TranslatorInterface
{
    private MessageLoaderInterface $loader;
    private string $locale;
    private string $defaultLocale;
    private string $domain;
    private array $messages = [];

    /**
     * Constrcutor
     */
    public function __construct(
        MessageLoaderInterface $loader, string $locale, string $domain = 'default'
        ) {
        if (! extension_loaded('intl')) {
            throw new RuntimeException('Intl extension not installed');
        }

        $this->locale = $this->defaultLocale = $locale;
        $this->domain = $domain;

        $this->setMessageLoader($loader);
    }

    /**
     * Gets the message loader used by the translator
     */
    public function getMessageLoader(): MessageLoaderInterface
    {
        return $this->loader;
    }

    /**
     * Sets the Message Loader
     *
     * @param MessageLoaderInterface $loader
     */
    public function setMessageLoader(MessageLoaderInterface $loader): static
    {
        $this->loader = $loader;

        $this->loadMessages();

        return $this;
    }

    /**
     * Sets the Locale
     */
    public function setLocale(string $locale): static
    {
        $this->locale = $locale;
        $this->loadMessages();

        return $this;
    }

    /**
     * Gets the locale
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Sets the Locale
     */
    public function setDefaultLocale(string $locale): static
    {
        $this->defaultLocale = $locale;

        return $this;
    }

    /**
     * Gets the locale
     */
    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }

    /**
     * Set Domain
     */
    public function setDomain(string $domain): static
    {
        $this->domain = $domain;
        $this->loadMessages();

        return $this;
    }

    /**
     * Gets the Translator
     *
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Load messages using inheritance.
     */
    private function loadMessages(): void
    {
        $this->messages = $this->loader->load($this->domain, $this->defaultLocale);

        foreach ([Locale::getPrimaryLanguage($this->locale),$this->locale] as $locale) {
            try {
                $this->messages = array_merge($this->messages, $this->loader->load($this->domain, $locale));
            } catch (MessageFileNotFound $exception) {
            }
        }
    }

    /**
     * Translates a message
     * @param array $values Values to be interpolated, the `count` value is reserved for simple pluralization engine
     */

    public function translate(?string $message, array $values = []): string
    {
        if (is_null($message)) {
            return '';
        }

        $message = $this->messages[$message] ?? $message;

        if (strpos($message, '|') !== false && isset($values['count'])) {
            $messages = explode('|', $message);

            // use count number if set, if not use the last.
            $message = $messages[$values['count']] ?? array_pop($messages);
        }

        return MessageFormatter::formatMessage($this->locale, $message, $values);
    }
}
