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
    protected ResourceBundleInterface $bundle;

    /**
     * Constructor
     *
     * @param ResourceBundle $resourceBundle the resource bundle for the default locale
     */
    public function __construct(protected ResourceBundleFactoryInterface $bundleFactory, string $defaultLocale = 'en_US')
    {
        if (! extension_loaded('intl')) {
            throw new RuntimeException('Intl extension not installed');
        }

        $this->defaultLocale = $defaultLocale;

        $this->locale = $defaultLocale;
        $this->bundle = $this->bundleFactory->create($this->locale);
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
     * Sets the Resource Bundle Factory
     */
    public function setResourceBundleFactory(ResourceBundleFactoryInterface $bundleFactory): static
    {
        $this->bundleFactory = $bundleFactory;
        $this->loadMessages();

        return $this;
    }

    /**
     * Gets the Resource Bundle Factory
     */
    public function getResourceBundleFactory(): ResourceBundleFactoryInterface
    {
        return $this->bundleFactory;
    }

    /**
     * Load messages using inheritance.
     */
    private function loadMessages(): void
    {
        foreach ([$this->locale,$this->defaultLocale] as $locale) {
            try {
                $this->bundle = $this->bundleFactory->create($locale);

                break;
            } catch (ResourceNotFoundException) {
            }
        }
    }

    /**
     * Translates a message
     *
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
