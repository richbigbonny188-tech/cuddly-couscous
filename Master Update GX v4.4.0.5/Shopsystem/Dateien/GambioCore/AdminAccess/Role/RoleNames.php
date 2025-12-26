<?php
/* --------------------------------------------------------------
   RoleNames.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Role;

/**
 * Interface RoleNames
 *
 * @package Gambio\Core\AdminAccess\Role
 */
interface RoleNames
{
    /**
     * @param int $languageId
     *
     * @return string
     */
    public function getName(int $languageId): string;
    
    
    /**
     * @param int    $languageId
     * @param string $name
     */
    public function addName(int $languageId, string $name): void;
}