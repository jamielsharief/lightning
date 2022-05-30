<?php declare(strict_types=1);

namespace Lightning\Translator;

use Countable;
use Traversable;
use ArrayIterator;
use IteratorAggregate;
use Lightning\Translator\Exception\ResourceNotFoundException;

class ResourceBundle implements IteratorAggregate, Countable
{
    /**
     * Constructor
     */
    final public function __construct(protected string $locale, protected string $path, protected array $messages)
    {
    }

    /**
     * Check if the resource bundle can return an entry for the name
     */
    public function has(string $key): bool
    {
        return isset($this->messages[$key]);
    }

    /**
     * Get an entry from the resource bundle
     *
     * @throws ResourceNotFoundException if the resource is not found
     */
    public function get(string $key): string
    {
        if (isset($this->messages[$key])) {
            return $this->messages[$key];
        }

        throw new ResourceNotFoundException(sprintf('No entry for `%s`', $key));
    }

    /**
     * Gets the locale for the Resource Bundle [read only]
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Gets the directory where the bundle files are stored
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Factory method
     */
    public static function create(string $locale, string $bundle): static
    {
        $path = sprintf('%s/%s.php', $bundle, $locale);
        if (! file_exists($path)) {
            throw new ResourceNotFoundException(sprintf('Resource bundle `%s` cannot be found', basename($path)));
        }

        return new static($locale,$bundle, include $path);
    }

    /**
     * IteratorAggregate interface
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->messages);
    }

    /**
     * Countable interface
     */
    public function count(): int
    {
        return count($this->messages);
    }

    /**
     * Gets the resource bundle as an array
     */
    public function toArray(): array
    {
        return $this->messages;
    }
}
