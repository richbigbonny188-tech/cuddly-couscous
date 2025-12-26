<?php
/* --------------------------------------------------------------
   GroupItemType.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Group\Model;

use Gambio\Core\AdminAccess\Group\GroupItem;
use Webmozart\Assert\Assert;

/**
 * Class GroupItemType
 *
 * @package Gambio\Core\AdminAccess\Group\Models
 */
class GroupItemType
{
    /**
     * @var string
     */
    private $value;
    
    
    /**
     * GroupItemType constructor.
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
     * @return GroupItemType
     */
    public static function create(string $value): GroupItemType
    {
        $allowedTypes = [
            GroupItem::PAGE_TYPE,
            GroupItem::CONTROLLER_TYPE,
            GroupItem::AJAX_HANDLER_TYPE,
            GroupItem::ROUTE_TYPE,
        ];
        Assert::oneOf($value,
                      $allowedTypes,
                      'Invalid group type value provided. Needs to be one of: ' . implode(', ', $allowedTypes)
                      . '; Got: %s');
        
        return new self($value);
    }
    
    
    /**
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }
}