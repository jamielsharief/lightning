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

use Lightning\View\Exception\ViewException;

class View
{
    private ViewCompilerInterface $compiler;
    private string $viewPath;
    private string $layoutPath;
    private string $viewExtension = '.php';
    protected ?string $layout = null;
    private array $attributes = [];

    private bool $inRender = false;
    private string $encoding;

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
        $this->encoding = mb_internal_encoding() ?: 'UTF-8';

        $this->initialize();
    }

    /**
     * Sets a view attribute
     *
     * @param string $name
     * @param mixed $value
     * @return static
     */
    public function setAttribute(string $name, mixed $value): static
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Gets an attribute from the view
     *
     * @param string $name
     * @return mixed
     */
    public function getAttribute(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
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
     * An immutable setter, returns a new view instance with the new layout setting
     *
     * @param string|null $layout
     * @return static
     */
    public function withLayout(?string $layout): static
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
    public function withViewPath(string $path): static
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
    public function withLayoutPath(string $path): static
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
     * @deprecated
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
        extract($this->attributes); # First
        extract($__variables__);

        ob_start();

        $this->inRender = true;

        require $__filename__;

        $this->inRender = false;

        return ob_get_clean();
    }

    /**
     * Convert special characters to HTML entities
     */
    protected function escape(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, $this->encoding);
    }
}
