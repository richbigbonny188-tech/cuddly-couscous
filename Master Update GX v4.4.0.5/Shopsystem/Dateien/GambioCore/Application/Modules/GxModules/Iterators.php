<?php
/* --------------------------------------------------------------
 Iterators.php 2020-07-09
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Modules\GxModules;

use DirectoryIterator;
use IteratorIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Trait Iterators
 * @package Gambio\Core\Application\Modules\GxModules
 */
trait Iterators
{
    /**
     * Utility method to create a directory iterator.
     *
     * @param string $path
     *
     * @return IteratorIterator|DirectoryIterator[]
     */
    private function directoryIterator(string $path): IteratorIterator
    {
        return new IteratorIterator(new DirectoryIterator($path));
    }
    
    
    /**
     * Utility method to create a recursive directory iterator.
     *
     * @param string $path
     *
     * @return RecursiveIteratorIterator|SplFileInfo[]
     */
    private function recursiveDirectoryIterator(string $path): RecursiveIteratorIterator
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $path, RecursiveDirectoryIterator::SKIP_DOTS
            ), RecursiveIteratorIterator::LEAVES_ONLY
        );
    }
}