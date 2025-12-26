<?php
/* --------------------------------------------------------------
 RenderTemplate.php 2020-09-04
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\TemplateEngine\Commands;

use Gambio\Core\TemplateEngine\Loader;

/**
 * Class RenderTemplate
 * @package Gambio\Core\TemplateEngine\Commands
 */
class LoadAdminLayout
{
    /**
     * @var Loader[]
     */
    private $loaders = [];
    
    
    /**
     * @return Loader[]
     */
    public function loaders(): array
    {
        return $this->loaders;
    }
    
    
    /**
     * Adds a new loader.
     *
     * @param Loader $loader
     */
    public function addLoader(Loader $loader): void
    {
        $this->loaders[] = $loader;
    }
}