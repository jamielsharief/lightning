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

use MessageFormatter;
use RuntimeException;
use Lightning\Translator\Exception\ResourceNotFoundException;

class Translator implements TranslatorInterface
{
    protected string $locale;
    protected string $defaultLocale;

    /**
     * Constructor
     *
     * @param ResourceBundle $resourceBundle the resource bundle for the default locale
     */
    public function __construct(protected ResourceBundle $bundle)
    {
        if (! extension_loaded('intl')) {
            throw new RuntimeException('Intl extension not installed');
        }

        $this->defaultLocale = $this->locale = $bundle->getLocale();
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
     * Gets the Locale
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Sets the Resource bundle
     */
    public function setResourceBundle(ResourceBundle $bundle): static
    {
        $this->bundle = $bundle;
        $this->loadMessages();

        return $this;
    }

    /**
     * Gets the Resource Bundle
     */
    public function getResourceBundle(): ResourceBundle
    {
        return $this->bundle;
    }

    /**
     * Load messages using inheritance.
     */
    private function loadMessages(): void
    {
        foreach ([$this->locale,$this->defaultLocale] as $locale) {
            try {
                $this->bundle = $this->createResourceBundle($locale, $this->bundle->getPath());

                break;
            } catch (ResourceNotFoundException) {
            }
        }
    }

    /**
     * Factory method
     */
    private function createResourceBundle(string $locale, string $bundle): ResourceBundle
    {
        return forward_static_call([get_class($this->bundle), 'create'], $locale, $bundle);
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

        $message = $this->bundle->has($message) ? $this->bundle->get($message) : $message;

        if (strpos($message, '|') !== false && isset($values['count'])) {
            $messages = explode('|', $message);

            // use count number if set, if not use the last.
            $message = $messages[$values['count']] ?? array_pop($messages);
        }

        return MessageFormatter::formatMessage($this->locale, $message, $values);
    }
}
