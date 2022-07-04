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

namespace Lightning\MessageQueue;

class Message
{
    private string $id;
    private object $body;
    private int $timestamp;

    /**
     * Constructor
     */
    public function __construct(object $body)
    {
        $this->id = bin2hex(random_bytes(16));
        $this->timestamp = time();
        $this->body = $body;
    }

    /**
     * Gets the message ID
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Gets the message body
     */
    public function getObject(): object
    {
        return $this->body;
    }

    /**
     * Gets the created timestamp for when this message was created
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
}
