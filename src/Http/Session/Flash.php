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

namespace Lightning\Http\Session;

use Traversable;
use ArrayIterator;
use IteratorAggregate;

class Flash implements IteratorAggregate
{
    protected SessionInterface $session;

    /**
     * Constructor
     *
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Sets a flash messages
     *
     * @param string $key
     * @param string $message
     * @return static
     */
    public function set(string $key, string $message): self
    {
        $flashed = $this->session->get('flash', []);

        $flashed[$key] = $message;
        $this->session->set('flash', $flashed);

        return $this;
    }

    /**
     * Checks if there is a flash message for the key
     *
     * @param string $key
     * @return boolean
     */
    public function has(string $key): bool
    {
        $flashed = $this->session->get('flash', []);

        return isset($flashed[$key]);
    }

    /**
     * Gets the flash message and removes from the session
     *
     * @param string $key
     * @return string|null
     */
    public function get(string $key): ?string
    {
        $flashed = $this->session->get('flash', []);

        $out = null;

        if (isset($flashed[$key])) {
            $out = $flashed[$key];
            unset($flashed[$key]);

            $this->session->set('flash', $flashed);
        }

        return $out;
    }

    /**
     * Gets all the flash messages and removes from session
     *
     * @return array
     */
    public function getMessages(): array
    {
        $messages = $this->session->get('flash', []);
        $this->session->set('flash', []);

        return $messages;
    }

    /**
     * IteratorAggregrate
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->getMessages());
    }
}
