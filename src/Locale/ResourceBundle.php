<?php declare(strict_types=1);

namespace Lightning\Locale;

use Countable;
use Traversable;
use ArrayIterator;
use IteratorAggregate;
use Lightning\Locale\Exception\ResourceNotFoundException;

class ResourceBundle implements ResourceBundleInterface, IteratorAggregate, Countable
{
    /**
     * Constructor
     */
    final public function __construct(protected Locale $locale, protected array $contents)
    {
    }

    /**
     * Check if the resource bundle can return an entry for the name
     */
    public function has(string $key): bool
    {
        return isset($this->contents[$key]);
    }

    /**
     * Get an entry from the resource bundle
     *
     * @throws ResourceNotFoundException if the resource is not found
     */
    public function get(string  $key): string
    {
        if (isset($this->contents[$key])) {
            return $this->contents[$key];
        }

        throw new ResourceNotFoundException(sprintf('No entry for `%s`', $key));
    }

    /**
     * Gets the locale for the Resource Bundle [read only]
     */
    public function getLocale(): Locale
    {
        return $this->locale;
    }

    /**
     * Factory method
     */
    public static function create(Locale $locale, string $bundle): static
    {
        $path = sprintf('%s/%s.php', $bundle, $locale->toString());

        if (! file_exists($path)) {
            throw new ResourceNotFoundException(sprintf('Resource bundle `%s` cannot be found', basename($path)));
        }

        return new static($locale,include $path);
    }

    /**
     * IteratorAggregate interface
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->contents);
    }

    /**
     * Countable interface
     */
    public function count(): int
    {
        return count($this->contents);
    }
}
