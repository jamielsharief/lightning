<?php declare(strict_types=1);

namespace Lightning\Translator;

use Countable;
use Traversable;
use ArrayIterator;
use IteratorAggregate;
use Lightning\Translator\Exception\ResourceNotFoundException;

class ResourceBundle implements ResourceBundleInterface, IteratorAggregate, Countable
{
    /**
     * Constructor
     */
    final public function __construct(protected array $messages)
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
