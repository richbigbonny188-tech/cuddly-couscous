<?php
/* --------------------------------------------------------------
 AbstractModule.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Modules;

/**
 * Class AbstractModule
 * @package Gambio\Core\Framework\Module
 */
abstract class AbstractModule implements Module
{
    /**
     * @inheritDoc
     */
    public function eventListeners(): ?array
    {
        return null;
    }
    
    
    /**
     * @inheritDoc
     */
    public function commandHandlers(): ?array
    {
        return null;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getRoutes(): ?array
    {
        return null;
    }
    
    
    /**
     * @inheritDoc
     */
    public function postRoutes(): ?array
    {
        return null;
    }
    
    
    /**
     * @inheritDoc
     */
    public function putRoutes(): ?array
    {
        return null;
    }
    
    
    /**
     * @inheritDoc
     */
    public function deleteRoutes(): ?array
    {
        return null;
    }
    
    
    /**
     * @inheritDoc
     */
    public function dependsOn(): ?array
    {
        return null;
    }
}