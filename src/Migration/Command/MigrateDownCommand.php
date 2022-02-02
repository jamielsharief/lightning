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

namespace Lightning\Migration\Command;

use Lightning\Console\Arguments;
use Lightning\Console\ConsoleIo;
use Lightning\Migration\Migration;
use Lightning\Console\AbstractCommand;
use Lightning\Console\ConsoleArgumentParser;

class MigrateDownCommand extends AbstractCommand
{
    protected string $name = 'migrate down';
    protected string $description = 'Migrates the database down.';
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

    protected function execute(Arguments $args, ConsoleIo $io): int
    {
        $this->migration->down(function ($migration) {
            $this->io->out("Rolling back migration <info>{$migration['name']}</info>");
            $this->io->nl();

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
