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

namespace Lightning\View;

use BadMethodCallException;
use Lightning\View\Exception\ViewException;

class View
{
    private ViewCompilerInterface $compiler;
    private string $viewPath;
    private string $layoutPath;
    private string $viewExtension = '.php';
    protected ?string $layout = null;

    private array $extensionMethods = [];

    private bool $inRender = false;

    /**
     * Constructor
     *
     * @param ViewCompilerInterface $compiler
     * @param string $path
     * @param string $layoutsFolder
     */
    public function __construct(ViewCompilerInterface $compiler, string $path, string $layoutsFolder = 'layouts')
    {
        $this->compiler = $compiler;
        $this->viewPath = $path;
        $this->layoutPath = $path . '/' . $layoutsFolder;

        $this->initialize();
    }

    /**
     * This is a hook that is called when the View is created
     *
     * @return void
     */
    protected function initialize(): void
    {
    }

    /**
     * Magic method which will trigger extension callbacks
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $callback = $this->extensionMethods[$name] ?? null;

        if ($callback) {
            return $callback(...$arguments);
        }

        throw new BadMethodCallException(sprintf('Unkown method `%s`', $name));
    }

    /**
     * Extend the View object by loading Helpers
     *
     * @internal this is nice and simple but there is no code completion which normally you can fix with docblock comments
     * when using objects.
     *
     * @param ViewExtensionInterface $extension
     * @return self
     */
    public function addExtension(ViewExtensionInterface $extension): self
    {
        foreach ($extension->getMethods() as $method) {
            $this->extensionMethods[$method] = [$extension,$method];
        }

        return $this;
    }

    /**
     * Adds a Helper to the View
     *
     * @param ViewHelperInterface $helper
     * @return self
     */
    public function addHelper(ViewHelperInterface $helper): self
    {
        $name = $helper->getName();
        $this->$name = $helper;

        return $this;
    }

    /**
     * An immutable setter, returns a new view instance with the new layout setting
     *
     * @param string|null $layout
     * @return static
     */
    public function withLayout(?string $layout): self
    {
        $view = clone $this;
        $view->layout = $layout;
        $this->layout = $layout;

        return $view;
    }

    /**
     * An immutable setter, returns a new view instance with the new path set
     *
     * @param string $path
     * @return static
     */
    public function withViewPath(string $path): self
    {
        $view = clone $this;
        $view->viewPath = $path;

        return $view;
    }

    /**
     * An immutable setter, returns a new view instance with the var set
     *
     * @param string $path
     * @return static
     */
    public function withLayoutPath(string $path): self
    {
        $view = clone $this;
        $view->layoutPath = $path;

        return $view;
    }

    /**
     * Renders a view and returns a response
     *
     * @param string $path articles/index or /var/www/app/resources/views/articles/index.php
     * @param array $variables
     * @return string
     */
    public function render(string $path, array $variables = []): string
    {
        $path = strncmp($path, '/', 1) === 0 ? $path : $this->viewPath  . '/' . trim($path, '/') . $this->viewExtension;

        $content = $this->renderView($path, $variables);

        if ($this->layout && ! $this->inRender) {
            $content = $this->renderView(
                $this->layoutPath . '/' . trim($this->layout, '/') . $this->viewExtension,
                ['rendered' => $content] + $variables
            );
        }

        return $content;
    }

    /**
     * Renders JSON
     *
     * @param array $payload
     * @param integer $jsonFlags
     * @return string
     */
    public function renderJson(array $payload, int $jsonFlags = 0): string
    {
        return json_encode($payload, $jsonFlags);
    }

    /**
     * Renders a partial view (without a layout), this can be used inside views
     *
     * @param string $path
     * @param array $variables
     * @return string
     */
    private function renderView(string $path, array $variables = []): string
    {
        if (! is_readable($path)) {
            throw new ViewException(sprintf('File `%s` not found', ltrim(str_replace($this->viewPath, '', $path), '/')));
        }

        $output = $this->doRender($this->compiler->compile($path), $variables);

        return $output;
    }

    /**
     * Render logic
     *
     * @param string $__filename__
     * @param array $__variables__
     * @return string
     */
    private function doRender(string $__filename__, array $__variables__ = []): string
    {
        extract($__variables__);

        ob_start();

        $this->inRender = true;

        require $__filename__;

        $this->inRender = false;

        return ob_get_clean();
    }
}
