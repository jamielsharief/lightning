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

namespace Lightning\Console;

/*
@source originphp

$formatter = new ConsoleHelpFormatter();

$formatter->setDescription([
    'Lorem ipsum dolor sit amet, adipisci nibh risus mollis sagittis, sed egestas erat, dui eu eros,' ,
    'facilisi nulla, wisi aenean id egestas. Ante orci vivamus fusce ac orci eget, id eget tincidunt',
    'nonummy diam.'
]);

$formatter->setUsage(['command [options] [arguments]']);
$formatter->setCommands([
    'app:do-something' => 'Lorem ipsum dolor sit amet, adipisci nibh risus mollis sagittis',
    'app:clear' => 'Ante orci vivamus fusce ac orci eget, id eget tincidunt'
]);

$formatter->setArguments([
    'url' => 'url to access',
    'password' => ['The password to use.','(default: something)']
]);

$formatter->setOptions([
    '-h,--help' => 'Displays this help',
    '-v,--verbose' => 'Displays verbose messaging'
]);

$formatter->setEpilog([
'Lorem ipsum dolor sit amet, adipisci nibh risus mollis sagittis, sed egestas erat, dui eu eros,' ,
'facilisi nulla, wisi aenean id egestas. Ante orci vivamus fusce ac orci eget, id eget tincidunt',
'nonummy diam.'
]);


$help = $formatter->generate();
*/

class ConsoleHelpFormatter
{
    /**
     * Output
     *
     * @var array
     */
    protected $out = [];
    /**
     * Description
     *
     * @var string
     */
    protected $description = null;
    /**
      * Usage
      *
      * @var string
      */
    protected $usage = null;

    /**
     * Commands
     *
     * @var array
     */
    protected $commands = [];
    /**
     * Arguments
     *
     * @var array
     */
    protected $arguments = [];
    /**
     * Options
     *
     * @var array
     */
    protected $options = [];
    /**
     * Epilog
     *
     * @var string
     */
    protected $epilog = null;

    protected $help = null;

    public const WIDTH = 72;

    /**
     * Generates the help
     *
     * @return string
     */
    public function generate(): string
    {
        $out = [];

        if ($this->description) {
            $out[] = $this->description;
            $out[] = '';
        }

        if ($this->usage) {
            $out[] = '<yellow>Usage:</yellow>';
            $out[] = $this->usage;
            $out[] = '';
        }

        $maxWidth = $this->calculateWidth();
        if ($this->commands) {
            $out[] = '<yellow>Commands:</yellow>';
            $out[] = $this->createTable($this->commands, $maxWidth);
            $out[] = '';
        }

        if ($this->arguments) {
            $out[] = '<yellow>Arguments:</yellow>';
            $out[] = $this->createTable($this->arguments, $maxWidth);
            $out[] = '';
        }

        if ($this->options) {
            $out[] = '<yellow>Options:</yellow>';
            $out[] = $this->createTable($this->options, $maxWidth);
            $out[] = '';
        }

        if ($this->help) {
            $out[] = '<yellow>Help:</yellow>';
            $out[] = $this->help;
            $out[] = '';
        }

        if ($this->epilog) {
            $out[] = $this->epilog;
            $out[] = '';
        }

        return implode("\n", $out);
    }

    /**
     * Calculates the width to be used when generating the help
     *
     * @return int
     */
    protected function calculateWidth(): int
    {
        $minWidth = 7;

        foreach ([$this->commands,$this->arguments,$this->options] as $table) {
            $maxWidth = $this->getMaxWidth($table);
            if ($maxWidth > $minWidth) {
                $minWidth = $maxWidth;
            }
        }

        return $minWidth + 1;
    }

    /**
     * Adds the description part of help
     *
     * @param string|array $description
     * @return self
     */
    public function setDescription($description): self
    {
        $this->description = $this->toText($description) ;

        return $this;
    }

    /**
     * Sets the usage
     *
     * @param string|array $usage
     * @return self
     */
    public function setUsage($usage): self
    {
        $usage = $this->toText($usage, "\n  ");
        $this->usage = $this->wrapText($usage, 2) ;

        return $this;
    }
    /**
     * Sets the commands to be used
     *
     * @param array $commands
     * @return self
     */
    public function setCommands(array $commands): self
    {
        $this->commands = $commands;

        return $this;
    }

    /**
     * Sets the options
     *
     * @param array $options
     * @return self
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Sets the arguments
     *
     * @param array $arguments
     * @return self
     */
    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Sets the epilog
     *
     * @param string|array $epilog
     * @return self
     */
    public function setEpilog($epilog): self
    {
        $this->epilog = $this->toText($epilog);

        return $this;
    }

    /**
     * Sets the help text
     *
     * @param string|array $help
     * @return self
     */
    public function setHelp($help): self
    {
        $help = $this->toText($help, "\n  ");
        $this->help = $this->wrapText($help, 2);

        return $this;
    }

    /**
     * Normalizes text to string
     *
     * @param string|array $mixed
     * @param string $glue
     * @return string
     */
    protected function toText($mixed, $glue = "\n"): string
    {
        if (is_string($mixed)) {
            $mixed = [$mixed];
        }

        return implode($glue, $mixed);
    }

    /**
     * Pads columns for a table
     *
     * @param array $array
     * @return string
     */
    protected function createTable(array $array, int $width = 20): string
    {
        $out = [];
        foreach ($array as $left => $right) {
            $left = str_pad($left, $width, ' ');
            if (is_string($right)) {
                $right = [$right];
            }
            foreach ($right as $row) {
                $out[] = "<green>{$left}</green>{$row}";
                $left = str_repeat(' ', strlen($left)); // Only show once
            }
        }

        return $this->indentText(implode("\n", $out), 2);
    }

    /**
     * Gets the maximum width for each items in the array
     *
     * @param array $array
     * @return int
     */
    protected function getMaxWidth(array $array): int
    {
        $maxLength = 0;
        foreach ($array as $left => $right) {
            $width = strlen($left);
            if ($width > $maxLength) {
                $maxLength = $width;
            }
        }

        return $maxLength;
    }

    /**
     * Only use for descriptions etc due to colors
     *
     * @param string $string
     * @param integer $indent
     * @return string
     */
    protected function wrapText(string $string, int $indent = 0): string
    {
        $string = wordwrap($string, self::WIDTH);
        if ($indent > 0) {
            $string = $this->indentText($string, $indent);
        }

        return $string;
    }

    /**
     * Indents text
     *
     * @param string $string
     * @param integer $indent
     * @return string
     */
    protected function indentText(string $string, int $indent): string
    {
        $padding = str_repeat(' ', $indent);

        return $padding . str_replace("\n", "\n{$padding}", $string);
    }
}
