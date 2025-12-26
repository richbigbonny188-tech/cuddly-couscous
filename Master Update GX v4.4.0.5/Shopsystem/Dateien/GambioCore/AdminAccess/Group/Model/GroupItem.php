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

namespace Gambio\Core\AdminAccess\Group\Model;

use Webmozart\Assert\Assert;

/**
 * Class GroupItem
 *
 * @package Gambio\Core\AdminAccess\Group\Models
 */
class GroupItem implements \Gambio\Core\AdminAccess\Group\GroupItem
{
    /**
     * @var GroupItemType
     */
    private $type;
    
    /**
     * @var string
     */
    private $descriptor;
    
    
    /**
     * GroupItem constructor.
     *
     * @param GroupItemType $type
     * @param string        $descriptor
     */
    private function __construct(GroupItemType $type, string $descriptor)
    {
        $this->type       = $type;
        $this->descriptor = $descriptor;
    }
    
    
    /**
     * @param GroupItemType $type
     * @param string        $descriptor
     *
     * @return GroupItem
     */
    public static function create(GroupItemType $type, string $descriptor): GroupItem
    {
        Assert::notWhitespaceOnly($descriptor, 'Descriptor can not be empty.');
        
        return new self($type, $descriptor);
    }
    
    
    /**
     * @inheritDoc
     */
    public function type(): string
    {
        return $this->type->value();
    }
    
    
    /**
     * @inheritDoc
     */
    public function descriptor(): string
    {
        return $this->descriptor;
    }
}