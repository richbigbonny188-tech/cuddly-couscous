<?php
/* --------------------------------------------------------------
 SearchAutoLoader.php 2020-09-14
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Modules\App\Commands;

/**
 * Class SearchAutoloader
 * @package Gambio\Core\Framework\Module\Commands
 */
class SearchAutoloader
{
    /**
     * @var array
     */
    private $autoloaderPaths = [];
    
    
    /**
     * Adds a new autoloader path.
     *
     * @param string $autoloaderPath
     */
    public function addAutoloader(string $autoloaderPath): void
    {
        if (is_file($autoloaderPath)) {
            $this->autoloaderPaths[] = $autoloaderPath;
        }
    }
    
    
    /**
     * Returns a list with all collected autoloader paths.
     *
     * @return array
     */
    public function autoloaderPaths(): array
    {
        return $this->autoloaderPaths;
    }
}