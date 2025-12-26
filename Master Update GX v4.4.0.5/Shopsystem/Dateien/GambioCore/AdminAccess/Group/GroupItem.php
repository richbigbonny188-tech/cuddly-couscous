<?php
/* --------------------------------------------------------------
   GroupItem.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Group;

/**
 * Interface GroupItem
 *
 * @package Gambio\Core\AdminAccess\Group
 */
interface GroupItem
{
    /**
     * Type for pages.
     */
    public const PAGE_TYPE = 'PAGE';
    
    /**
     * Type for HTTP controllers.
     */
    public const CONTROLLER_TYPE = 'CONTROLLER';
    
    /**
     * Type for ajax handlers.
     */
    public const AJAX_HANDLER_TYPE = 'AJAX_HANDLER';
    
    /**
     * Type for HTTP routes.
     */
    public const ROUTE_TYPE = 'ROUTE';
    
    
    /**
     * @return string
     */
    public function type(): string;
    
    
    /**
     * @return string
     */
    public function descriptor(): string;
}