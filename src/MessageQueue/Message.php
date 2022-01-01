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

namespace Lightning\MessageQueue;

class Message
{
    private string $id;
    private string $body;
    private int $timestamp;

    /**
     * Constructor
     *
     * @param string $body
     */
    public function __construct(string $body)
    {
        $this->id = bin2hex(random_bytes(16));
        $this->timestamp = time();
        $this->body = $body;
    }

    /**
     * Gets the message ID
     *
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Gets the message body
     *
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Gets the created timestamp for when this message was created
     *
     * @return integer
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
}
