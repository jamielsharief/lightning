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

namespace Lightning\Migration\Command;

use Lightning\Console\Arguments;
use Lightning\Console\ConsoleIo;
use Lightning\Migration\Migration;
use Lightning\Console\AbstractCommand;
use Lightning\Console\ConsoleArgumentParser;

class MigrateUpCommand extends AbstractCommand
{
    protected string $name = 'migrate up';
    protected string $description = 'Migrates the database up.';

    protected int $count = 0;

    protected Migration $migration;

    /**
     * Constructor
     *
     * @param ConsoleArgumentParser $parser
     * @param ConsoleIo $io
     * @param Migration $migration
     */
    public function __construct(ConsoleArgumentParser $parser, ConsoleIo $io, Migration $migration)
    {
        $this->migration = $migration;
        parent::__construct($parser, $io);
    }

    /**
     * Executes the command
     *
     * @param Arguments $args
     * @param ConsoleIo $io
     * @return integer
     */
    protected function execute(Arguments $args, ConsoleIo $io): int
    {
        $this->migration->up(function ($migration) {
            $this->io->out("Running migration <info>{$migration['name']}</info>");

            foreach ($migration['statements'] as $sql) {
                $this->io->out(sprintf('<green> > </green> %s', $sql));
                $this->io->nl();
            }
            $this->count++;
        });

        $this->out(sprintf('Ran %d migration(s)', $this->count));

        return self::SUCCESS;
    }
}
