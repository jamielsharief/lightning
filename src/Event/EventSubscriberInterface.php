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

interface EventSubscriberInterface
{
    /**
     * Return an array of eventTypes and methods
     *
     * [
     *  'Order.complete' => 'sendEmail'
     *  'Order.newCustomer' => ['sendSMS',100],
     *   AfterFindEvent::class => [
     *      ['addThis'],
     *      ['addThat',100]
     *   ]
     * ]
     *
     * @return array
     */
    public function getSubscribedEvents(): array;
}
