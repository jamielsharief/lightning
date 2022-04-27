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

namespace Lightning\Http\Session;

use Redis;

class RedisSession extends AbstractSession
{
    private Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    private function sessionKey(): string
    {
        return 'session_' . $this->id;
    }

    public function start(?string $id): bool
    {
        if ($this->isStarted) {
            return false;
        }

        $this->id = $id ?: $this->createId();

        $data = $this->redis->get($this->sessionKey());

        $this->session = $data ? json_decode($data, true) : [];

        return $this->isStarted = true;
    }

    public function destroy(): void
    {
        if ($this->isStarted) {
            $this->redis->delete($this->sessionKey());
        }
        parent::destroy();
    }

    public function close(): bool
    {
        if (! $this->isStarted) {
            return false;
        }

        $this->isStarted = false;

        return $this->redis->set($this->sessionKey(), json_encode($this->session));
    }
}
