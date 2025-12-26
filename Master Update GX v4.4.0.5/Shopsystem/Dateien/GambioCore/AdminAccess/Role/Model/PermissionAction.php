<?php
/* --------------------------------------------------------------
   PermissionAction.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Role\Model;

use Webmozart\Assert\Assert;

/**
 * Class PermissionAction
 *
 * @package Gambio\Core\AdminAccess\Role\Model
 */
class PermissionAction implements \Gambio\Core\AdminAccess\Role\PermissionAction
{
    /**
     * @var string
     */
    private $value;
    
    
    /**
     * PermissionAction constructor.
     *
     * @param string $value
     */
    private function __construct(string $value)
    {
        $this->value = $value;
    }
    
    
    /**
     * @param string $value
     *
     * @return PermissionAction
     */
    public static function create(string $value): PermissionAction
    {
        $allowedActions = [
            \Gambio\Core\AdminAccess\Role\PermissionAction::READ,
            \Gambio\Core\AdminAccess\Role\PermissionAction::WRITE,
            \Gambio\Core\AdminAccess\Role\PermissionAction::DELETE,
        ];
        Assert::oneOf($value,
                      $allowedActions,
                      'Action must be one of: ' . implode(', ', $allowedActions) . '; Got: %s');
        
        return new self($value);
    }
    
    
    /**
     * @inheritDoc
     */
    public function value(): string
    {
        return $this->value;
    }
}