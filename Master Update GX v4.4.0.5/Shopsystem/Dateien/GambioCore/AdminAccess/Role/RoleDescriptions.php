<?php
/* --------------------------------------------------------------
   RoleDescriptions.php 2020-05-29
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
 * Interface RoleDescriptions
 *
 * @package Gambio\Core\AdminAccess\Role
 */
interface RoleDescriptions
{
    /**
     * @param int $languageId
     *
     * @return string
     */
    public function getDescription(int $languageId): string;
    
    
    /**
     * @param int    $languageId
     * @param string $description
     */
    public function addDescription(int $languageId, string $description): void;
}