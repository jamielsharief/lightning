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

namespace Lightning\Console;

use InvalidArgumentException;

/**
 * ConsoleIO
 *
 * @internal on formatting
 */
class ConsoleIo
{
    /**
     * @var resource $stream
     */
    protected $stdout = STDOUT;

    /**
     * @var resource $stream
     */
    protected $stdin = STDIN;

    /**
     * @var resource $stream
     */
    protected $stderr = STDERR;

    public const QUIET = 0;
    public const NORMAL = 1;
    public const VERBOSE = 2;

    /**
     * Output mode RAW
     */
    public const RAW = 3;

    /**
     * Output mode PLAIN - no colors
     */
    public const PLAIN = 4;

    /**
     * Outmode COLOR if supported by terminal
     */
    public const COLOR = 5;

    protected int $outputLevel = self::NORMAL;
    protected int $outputMode = self::COLOR;

    protected array $statuses = [
        'ok' => 'green',
        'warning' => 'yellow',
        'error' => 'red'
    ];

    /**
     * Array of styles that can be used
     *
     * @var array
     */
    protected array $styles = [
        // levels
        'emergency' => '97;41',
        'alert' => '97;41',
        'critical' => '1;31',
        'error' => '31',
        'warning' => '33',
        'notice' => '36',
        'info' => '32',
        'debug' => '37',

        // single color style
        'default' => '39',
        'black' => '30',
        'red' => '31',
        'green' => '32',
        'yellow' => '33',
        'blue' => '34',
        'magenta' => '35',
        'cyan' => '36',
        'grey' => '90',
        'lightGrey' => '37',
        'lightRed' => '91',
        'lightGreen' => '92',
        'lightYellow' => '93',
        'lightBlue' => '94',
        'lightMagenta' => '95',
        'lightCyan' => '96',
        'white' => '97'
    ];

    protected array $foregroundColors = [
        'default' => '39',
        'black' => '30',
        'red' => '31',
        'green' => '32',
        'yellow' => '33',
        'blue' => '34',
        'magenta' => '35',
        'cyan' => '36',
        'grey' => '90',
        'lightGrey' => '37',
        'lightRed' => '91',
        'lightGreen' => '92',
        'lightYellow' => '93',
        'lightBlue' => '94',
        'lightMagenta' => '95',
        'lightCyan' => '96',
        'white' => '97'
    ];

    protected array $backgroundColors = [
        'default' => '49',
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',
        'cyan' => '46',
        'grey' => '100',
        'lightGrey' => '47',
        'lightRed' => '101',
        'lightGreen' => '102',
        'lightYellow' => '103',
        'lightBlue' => '104',
        'lightMagenta' => '105',
        'lightCyan' => '106',
        'white' => '107'
    ];

    /**
     * Constructor
     *
     * @param resource $stdout
     * @param resource $stderr
     * @param resource $stdin
     */
    public function __construct($stdout = STDOUT, $stderr = STDERR, $stdin = STDIN)
    {
        $this->stdout = $stdout;
        $this->stderr = $stderr;
        $this->stdin = $stdin;

        $this->outputMode = stream_isatty(STDOUT) ? self::COLOR : self::PLAIN;

        // @see https://no-color.org/
        if (getenv('NO_COLOR')) {
            $this->outputMode = self::PLAIN;
        }
    }

    /**
     * Sets the output level
     *
     * @param integer $level self::QUIET, self::NORMAL, self::VERBOSE
     * @return static
     */
    public function setOutputLevel(int $level): self
    {
        if (! in_array($level, [self::NORMAL,self::QUIET,self::VERBOSE])) {
            throw new InvalidArgumentException(sprintf('Invalid output level %d', $level));
        }

        $this->outputLevel = $level;

        return $this;
    }

    /**
     * Sets the output mode
     *
     * @param integer $mode
     * @return static
     */
    public function setOutputMode(int $mode): self
    {
        if (! in_array($mode, [self::RAW,self::PLAIN,self::COLOR])) {
            throw new InvalidArgumentException(sprintf('Invalid output mode %d', $mode));
        }

        $this->outputMode = $mode;

        return $this;
    }

    /**
     * Sets a style
     *
     * @param string $name
     * @param array $options The following options are supported:
     *  - foreground: e.g white
     *  - background: e.g. lightRed
     *  - bold: true
     * @return static
     */
    public function setStyle(string $name, array $options): self
    {
        $this->styles[$name] = $this->createAnsiStyle($options);

        return $this;
    }

    /**
     * Sets a status
     *
     * @param string $name
     * @param string $color
     * @return self
     */
    public function setStatus(string $name, string $color): self
    {
        $this->statuses[strtolower($name)] = $color;

        return $this;
    }

    /**
     * Writes to stdout
     *
     * @param string|array $message
     * @param int $newLines
     * @param int $outputLevel
     * @return static
     */
    public function out($message, int $newLines = 1, int $outputLevel = self::NORMAL): self
    {
        if ($outputLevel <= $this->outputLevel) {
            if (is_array($message)) {
                $message = implode(PHP_EOL, $message);
            }
            $this->writeStdout($this->format($message) . ($newLines ? str_repeat(PHP_EOL, $newLines) : null));
        }

        return $this;
    }

    /**
     * Writes to stderr
     *
     * @param string|array $message
     * @param int $newLines
     * @return static
     */
    public function err($message, int $newLines = 1): self
    {
        if (is_array($message)) {
            $message = implode(PHP_EOL, $message);
        }

        $this->writeStderr($this->format($message) . ($newLines ? str_repeat(PHP_EOL, $newLines) : null));

        return $this;
    }

    /**
     * Gets input from stdin
     *
     * @param mixed $default value
     * @return mixed
     */
    public function in($default = null)
    {
        $input = rtrim($this->readStdin(), PHP_EOL);

        return $input === '' ? $default : $input;
    }

    /**
     * Outputs a new line
     *
     * @return static
     */
    public function nl(int $lines = 1): self
    {
        $this->writeStdout(str_repeat(PHP_EOL, $lines));

        return $this;
    }

    /**
     * Outputs a horiztonal rule
     *
     * @return static
     */
    public function hr(): self
    {
        $this->out(str_repeat('-', 80));

        return $this;
    }

    /**
     * Asks for input
     *
     * @param string $message
     * @param mixed $default
     * @return mixed
     */
    public function ask(string $message, $default = null)
    {
        if ($default) {
            $message = sprintf('%s [%s]', $message, (string) $default);
        }
        $this->out($message);
        $this->out('<white>></white> ', 0);

        return $this->in($default);
    }

    /**
     * Asks a question with available choices
     *
     * @param string $message
     * @param array $choices
     * @return mixed
     */
    public function askChoice(string $message, array $choices)
    {
        $result = $this->ask($message);

        while (! in_array($result, $choices)) {
            $result = $this->askChoice($message, $choices);
        }

        return $result;
    }

    /**
     * Displays an info alert
     *
     * @param string $secondary
     * @param array $options
     * @return static
     */
    public function info(string $text, ?string $secondary = null, array $options = []): self
    {
        $options += ['background' => 'blue', 'color' => 'white', 'bold' => true];

        $this->out($this->colorize(sprintf(' %s ', $text), $options) . ($secondary ? ' ' . $secondary : null));

        return $this;
    }

    /**
     * Displays a success alert
     *
     * @param string $secondary
     * @param array $options
     * @return static
     */
    public function success(string $text, ?string $secondary = null, array $options = []): self
    {
        $options += ['background' => 'green', 'color' => 'white', 'bold' => true];

        $this->out($this->colorize(sprintf(' %s ', $text), $options) . ($secondary ? ' ' . $secondary : null));

        return $this;
    }

    /**
     * Displays a warning alert (stderr)
     *
     * @param string $secondary
     * @param array $options
     * @return static
    */
    public function warning(string $text, ?string $secondary = null, array $options = []): self
    {
        $options += ['background' => 'yellow', 'color' => 'white', 'bold' => true];

        $this->err($this->colorize(sprintf(' %s ', $text), $options) . ($secondary ? ' ' . $secondary : null));

        return $this;
    }

    /**
     * Displays an error alert (stderr)
     *
     * @param string $secondary
     * @param array $options
     * @return static
     */
    public function error(string $text, ?string $secondary = null, array $options = []): self
    {
        $options += ['background' => 'red', 'color' => 'white', 'bold' => true];

        $this->err($this->colorize(sprintf(' %s ', $text), $options) . ($secondary ? ' ' . $secondary : null));

        return $this;
    }

    /**
     * Displays a status [ OK ] Something
     *
     * @param string $status
     * @param string $message
     * @return static
     */
    public function status(string $status, string $message): self
    {
        if (! isset($this->statuses[$status])) {
            throw new InvalidArgumentException(sprintf('Unkown status `%s`', $status));
        }
        $color = $this->statuses[$status];
        $this->out("<white>[</white> <{$color}>" . strtoupper($status) . "</{$color}> <white>] {$message}</white>");

        return $this;
    }

    /**
     * Draws a progress bar.
     *
     * @param integer $value
     * @param integer $max
     * @param array $options
     * @return void
     * @see http://ascii-table.com/ansi-escape-sequences-vt-100.php
     */
    public function progressBar(int $value, int $max, array $options = []): void
    {
        $options += ['color' => 'blue'];

        $percentage = floor(($value * 100) / $max);
        $pending = 100 - $percentage;

        if ($pending % 2 !== 0) {
            $pending ++;
        }

        $percentageString = str_pad((string) $percentage . '%', 4, ' ', STR_PAD_LEFT);

        $block = $this->outputMode === self::COLOR ? $this->colorize('█', ['color' => $options['color']]) : '█';
        $empty = $this->outputMode === self::COLOR ? $this->colorize('█', ['color' => 'grey']) : ' ';

        $progressBar = str_repeat($block, (int) floor($percentage / 2)) .  str_repeat($empty, (int) floor($pending / 2)) ;

        if ($this->outputMode === self::COLOR) {
            $percentageString = $this->colorize($percentageString, ['color' => $options['color']]);
        } else {
            $percentageString = sprintf('[ %s ]', $percentageString);
        }

        $this->writeStdout("\r{$progressBar} {$percentageString}");

        if ($value === $max) {
            $this->nl();
        }
    }

    /**
     * Writes to stream
     *
     * @param string $data
     * @return void
     */
    protected function writeStdout(string $data): void
    {
        fwrite($this->stdout, $data);
    }

    /**
     * Writes to stream
     *
     * @param string $data
     * @return void
     */
    protected function writeStderr(string $data): void
    {
        fwrite($this->stderr, $data);
    }

    /**
     * Reads
     *
     * @return string
     */
    protected function readStdin(): string
    {
        return fread($this->stdin, 8192);
    }

    /**
     * Colorizes the text using an array of settings
     *
     * @param string $text
     * @param array $options
     * @return string
     */
    protected function colorize(string $text, array $options = []): string
    {
        if ($this->outputMode === self::RAW) {
            return $text;
        } elseif ($this->outputMode === self::PLAIN) {
            return $this->stripTags($text);
        }

        $style = $this->createAnsiStyle($options);

        return  "\033[{$style}m{$text}\033[0m";
    }

    /**
     * Replaces tags as per output mode
     *
     * @internal This is not intented to handle nested tags
     *
     * @param string $text
     * @return string
     */
    protected function format(string $text): string
    {
        if ($this->outputMode === self::RAW) {
            return $text;
        } elseif ($this->outputMode === self::PLAIN) {
            return $this->stripTags($text);
        }

        // Replace tags with colors
        if (preg_match_all('/<([a-z0-9]+)>(.*?)<\/(\1)>/ims', $text, $matches)) {
            foreach ($matches[1] as $key => $tag) {
                $style = $this->styles[$tag] ?? null;
                if ($style) {
                    $string = $matches[2][$key];
                    $text = str_replace($matches[0][$key], "\033[0;{$style}m{$string}\033[0m", $text);
                }
            }
        }

        return $text;
    }

    /**
     * Strips tags
     *
     * @param string $message
     * @return string
     */
    private function stripTags(string $message): string
    {
        $tags = array_keys($this->styles);

        return preg_replace('/<\/?(' . implode('|', $tags) . ')>/', '', $message);
    }

    /**
     * Create an ANSI style from an array
     *
     * @param array $options
     * @return string
     */
    private function createAnsiStyle(array $options): string
    {
        $options += ['color' => 'default','background' => 'default','bold' => false,'italic' => false,'underline' => false];

        if (! isset($this->foregroundColors[$options['color']])) {
            throw new InvalidArgumentException(sprintf('Invalid color `%s`', $options['color']));
        }

        if (! isset($this->backgroundColors[$options['background']])) {
            throw new InvalidArgumentException(sprintf('Invalid background color `%s`', $options['background']));
        }

        $set = [0, $this->foregroundColors[$options['color']],$this->backgroundColors[$options['background']]];

        if ($options['bold']) {
            $set[] = 1;
        }
        if ($options['italic']) {
            $set[] = 3;
        }

        if ($options['underline']) {
            $set[] = 4;
        }

        return implode(';', $set);
    }
}
