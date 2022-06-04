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

namespace Lightning\TemplateRenderer;

use Lightning\TemplateRenderer\Exception\TemplateRendererException;

class TemplateRenderer
{
    private ?string $cachePath;

    private array $attributes = [];

    private bool $inRender = false;
    private string $encoding;

    protected ?string $layout = null;

    /**
     * Constructor
     */
    public function __construct(private string $path, ?string $cachePath = null)
    {
        $this->encoding = mb_internal_encoding() ?: 'UTF-8';

        $this->cachePath = $cachePath ?: sys_get_temp_dir() . '/templates';

        if (! is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0775, true);
        }

        $this->initialize();
    }

    /**
     * This is a hook that is called when this object is created
     */
    protected function initialize(): void
    {
    }

    /**
     * Sets a view attribute

     */
    public function setAttribute(string $name, mixed $value): static
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Gets an attribute from the view
     */
    public function getAttribute(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Sets the path
     */
    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * An immutable setter, returns a new view instance with the new path set
     */
    public function withPath(string $path): static
    {
        $template = clone $this;
        $template->templatePath = $path;

        return $template;
    }

    /**
     * Sets the layout
     */
    public function setLayout(string $layout): static
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Gets the layout
     */
    public function getLayout(): ?string
    {
        return $this->layout;
    }

    /**
     * An immutable setter, returns a new view instance with the new layout setting
     */
    public function withLayout(?string $layout): static
    {
        $template = clone $this;
        $template->layout = $layout;
        $this->layout = $layout;

        return $template;
    }

    /**
     * Renders a view and returns a response
     *
     * @param string $path articles/index or /var/www/app/resources/views/articles/index.php

     */
    public function render(string $path, array $variables = []): string
    {
        $path = strncmp($path, '/', 1) === 0 ? $path : $this->path . '/' . trim($path, '/') . '.php';

        $content = $this->renderTemplate($path, $variables);

        if ($this->layout && ! $this->inRender) {
            $content = $this->renderTemplate(
                $this->path . '/' . trim($this->layout, '/') . '.php',
                ['content' => $content] + $variables
            );
        }

        return $content;
    }

    /**
     * Renders a partial template (without a layout), this can be used inside views
     */
    private function renderTemplate(string $path, array $variables = []): string
    {
        if (! is_readable($path)) {
            throw new TemplateRendererException(sprintf('File `%s` not found', ltrim(str_replace($this->path, '', $path), '/')));
        }

        $output = $this->doRender($this->compile($path), $variables);

        return $output;
    }

    /**
     * Render logic
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

    /**
     * Gets the compiled view filename, if the view has been changed or the compiled version does not exist
     * then it will be compiled.
     */
    private function compile(string $path): string
    {
        $compiledFilename = $this->cachePath . '/' . md5($path) . '.php';

        $isCompiled = file_exists($compiledFilename) && filemtime($compiledFilename) > filemtime($path);
        if (! $isCompiled && file_put_contents($compiledFilename, $this->compileTemplate($path)) === false) {
            throw new TemplateRendererException('Error saving compiled view');
        }

        return $compiledFilename;
    }

    private function compileTemplate(string $path): string
    {
        return preg_replace('/\{\{\s(.+?)\s\}\}/', '<?= $this->escape($1) ?>', file_get_contents($path));
    }
}
