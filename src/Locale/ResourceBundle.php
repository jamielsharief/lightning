<?php declare(strict_types=1);

namespace Lightning\Locale;

use Countable;
use Traversable;
use ArrayIterator;
use IteratorAggregate;
use Lightning\Locale\Exception\ResourceNotFoundException;

class ResourceBundle implements ResourceBundleInterface, IteratorAggregate, Countable
{
    protected string $basePath;
    protected Locale $locale;
    protected string $name;
    protected array $contents = [];

    /**
     * Constructor
     */
    public function __construct(string $basePath, Locale $locale, string $name = 'resources')
    {
        $this->basePath = $basePath;
        $this->name = $name;

        $this->setLocale($locale);
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
     * Set the locale for the resource bundle
     */
    public function setLocale(Locale $locale): static
    {
        $this->locale = $locale;

        $this->contents = $this->loadContents();

        return $this;
    }

    /**
     * Gets the Locale for this resource bundle
     */
    public function getLocale(): Locale
    {
        return $this->locale;
    }

    /**
     * Return an instance of resource bundle with a locale
     */
    public function withLocale(Locale $locale): static
    {
        return (clone $this)->setLocale($locale);
    }

    /**
     * Sets the resource bundle name (domains in gettext)
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        $this->contents = $this->loadContents();

        return $this;
    }

    /**
     * Gets the resource bundle name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Return an instance of the resource bundle with the name
     */
    public function withName(string $name): static
    {
        return (clone $this)->setName($name);
    }

    /**
     * Gets the path for the bundle with the extension
     */
    protected function getResourceBundlePath(string $extension): string
    {
        return sprintf('%s/%s.%s.%s', $this->basePath, $this->name, $this->locale->toString(), $extension);
    }

    /**
     * Load the resource bundle contents from file
     */
    protected function loadContents(): array
    {
        $path = $this->getResourceBundlePath('php');
        if (! file_exists($path)) {
            throw new ResourceNotFoundException(sprintf('Resource bundle `%s` cannot be found', basename($path)));
        }

        return include $path;
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
