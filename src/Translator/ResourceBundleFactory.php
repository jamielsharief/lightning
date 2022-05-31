<?php declare(strict_types=1);

namespace Lightning\Translator;

use Lightning\Translator\Exception\ResourceNotFoundException;

class ResourceBundleFactory implements ResourceBundleFactoryInterface
{
    /**
     * Constructor
     *
     * @param string $bundle the directory where the files are stored
     */
    public function __construct(protected string $bundle)
    {
    }

    /**
     * Factory method
     */
    public function create(string $locale): ResourceBundle
    {
        $path = sprintf('%s/%s.%s', $this->bundle, $locale, 'php');
        if (! file_exists($path)) {
            throw new ResourceNotFoundException(sprintf('Resource bundle `%s` cannot be found', basename($path)));
        }

        return new ResourceBundle(include $path);
    }
}
