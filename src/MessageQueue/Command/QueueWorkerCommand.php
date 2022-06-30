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

namespace Lightning\MessageQueue\Command;

use Throwable;

use Lightning\Console\Arguments;
use Lightning\Console\ConsoleIo;
use Lightning\Console\AbstractCommand;
use Lightning\MessageQueue\MessageConsumer;
use Lightning\MessageQueue\WorkerInterface;
use Lightning\Console\ConsoleArgumentParser;

/**
 * QueueWorkerCommand
 *
 * @internal This design should never process multiple queues, a worker for each queue
 */
class QueueWorkerCommand extends AbstractCommand
{
    protected string $name = 'queue:worker';
    protected string $description = 'message queue worker';

    /**
     * Constructor
     */
    public function __construct(ConsoleArgumentParser $parser, ConsoleIo $io, protected MessageConsumer $messageConsumer)
    {
        parent::__construct($parser, $io);
    }

    /**
     * Constructor hook
     */
    protected function initialize(): void
    {
        $this->addArgument('queue', [
            'description' => 'The queue where to get the messages from',
            'type' => 'string',
            'required' => true
        ]);

        $this->addOption('daemon', [
            'description' => 'Run in daemon mode',
            'type' => 'boolean',
            'short' => 'd',
            'default' => false
        ]);
    }

    /**
     * Command logic is here
     */
    protected function execute(Arguments $args, ConsoleIo $io): int
    {
        $io->setStatus('skipped', 'yellow');

        $queue = $args->getArgument('queue');

        $args->getOption('daemon') ? $this->daemon($queue) : $this->work($queue);

        return self::SUCCESS;
    }

    /**
     * Check messages every second
     */
    protected function daemon(string $queue): void
    {
        if (! $this->work($queue)) {
            sleep(1);
        }
        $this->daemon($queue);
    }

    /**
     * Works the queue and processes the message, if one is found, immediately check for another one
     */
    protected function work(string $queue): bool
    {
        $message = $this->messageConsumer->receiveFrom($queue);
        if (! $message) {
            return false;
        }

        $object = @unserialize($message->getBody()); # silent E_NOTICE
        if ($object instanceof WorkerInterface) {
            $what = $message->getId() . ' ' . get_class($object);

            try {
                $object->run();
                $this->io->status('ok', $what);
            } catch (Throwable $exception) {
                $this->io->status('error', $what);
            }
        } else {
            $this->io->status('skipped', sprintf('Message with id `%s` is not a worker', $message->getId()));
        }

        return $this->work($queue);
    }
}
