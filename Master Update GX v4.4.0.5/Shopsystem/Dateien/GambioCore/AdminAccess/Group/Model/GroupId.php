<?php
/* --------------------------------------------------------------
   GroupId.php 2020-05-29
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
 * Class GroupId
 *
 * @package Gambio\Core\AdminAccess\Group\Models
 */
class GroupId implements \Gambio\Core\AdminAccess\Group\GroupId
{
    /**
     * @var int
     */
    private $value;
    
    
    /**
     * GroupId constructor.
     *
     * @param int $value
     */
    private function __construct(int $value)
    {
        $this->value = $value;
    }
    
    
    /**
     * @param int $value
     *
     * @return GroupId
     */
    public static function create(int $value): GroupId
    {
        Assert::greaterThan($value, 0, 'Group ID need to be greater than 0. Got: %s');
        
        return new self($value);
    }
    
    
    /**
     * @inheritDoc
     */
    public function value(): int
    {
        return $this->value;
    }
}