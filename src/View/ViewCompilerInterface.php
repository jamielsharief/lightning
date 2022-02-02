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

 interface ViewCompilerInterface
 {
     /**
      * Compiles a View and returns the compiled filename
      *
      * @param string $path
      * @return string
      */
     public function compile(string $path): string;
 }
