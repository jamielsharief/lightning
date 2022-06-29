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

namespace Lightning\Event;

/**
 * SubscriberInterface
 * @internal dont be confused by simplicity, this is basically a map to methods inside the subscriber class. If you want to implement something like
 * priorities then that should be set for the subscriber as a whole in the extended method.
 */
interface SubscriberInterface
{
    /**
     * Gets an array of event and method names e.g.
     *
     *  [BeforeFind::class => 'beforeFind']
     */
    public function getSubscribedEvents(): array;
}
