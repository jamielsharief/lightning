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

namespace Lightning\Worker\Command;

use Lightning\Console\Arguments;
use Lightning\Console\ConsoleIo;
use Lightning\Console\AbstractCommand;
use Lightning\MessageQueue\MessageConsumer;
use Lightning\Console\ConsoleArgumentParser;

/**
 * QueueWorkerCommand
 *
 * @internal This design should NEVER process multiple queues, a worker for each queue. This is basically the MessageConsumer
 */
class QueueWorkerCommand extends AbstractCommand
{
    protected string $name = 'queue:worker';
    protected string $description = 'message queue worker';

    protected $isStopped = false;

    /**
     * Constructor
     */
    public function __construct(ConsoleArgumentParser $parser, ConsoleIo $io, protected MessageConsumer $consumer)
    {
        parent::__construct($parser, $io);

        if (extension_loaded('pcntl')) {
            pcntl_async_signals(true);
            pcntl_signal(SIGTERM, [$this, 'stopDaemon']);
            pcntl_signal(SIGINT, [$this, 'stopDaemon']);
        }
    }

    /**
     * Constructor hook
     */
    protected function initialize(): void
    {
        $this->addArgument('queue', [
            'description' => 'The queue where to get the messages from',
            'type' => 'string'
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
        if ($source = $args->getArgument('queue')) {
            $this->consumer->setSource($source);
        }

        $args->getOption('daemon') ? $this->consumer->receive() : $this->consume();

        return self::SUCCESS;
    }

    private function consume(): void
    {
        if ($this->consumer->receiveNoWait()) {
            $this->consume();
        }
    }

    public function stopDaemon(): void
    {
        $this->out();
        $this->out('<green>> </green><white>Gracefully stopping... (press </white><yellow>Ctrl+C</yellow><white> again to force)</white>');
        $this->consumer->stop();
    }
}
