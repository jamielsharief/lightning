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

namespace Lightning\View;

use Lightning\View\Exception\ViewException;

class ViewCompiler implements ViewCompilerInterface
{
    protected string $viewPath;
    protected string $cachedPath;

    /**
     * Constructor
     *
     * @param string $viewPath
     * @param string $cachedPath
     */
    public function __construct(string $viewPath, string $cachedPath)
    {
        $this->viewPath = $viewPath;
        $this->cachedPath = $cachedPath;
    }

    /**
     * Gets the compiled view filename, if the view has been changed or the compiled version does not exist
     * then it will be compiled.
     *
     * @param string $path
     * @return string
     */
    public function compile(string $path): string
    {
        $compiledFilename = $this->cachedPath . '/' . md5($path) . '.php';

        if (file_exists($compiledFilename) && filemtime($compiledFilename) > filemtime($path)) {
            return $compiledFilename;
        }

        if (file_put_contents($compiledFilename, $this->compileView($path)) === false) {
            throw new ViewException('Error saving compiled view');
        }

        return $compiledFilename;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function compileView(string $path): string
    {
        return preg_replace('/\{\{\s(.+?)\s\}\}/', '<?php echo htmlspecialchars($1, ENT_QUOTES) ?>', file_get_contents($path));
    }
}
