<?php
/* --------------------------------------------------------------
   ParentGroupId.php 2020-05-29
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
 * Class ParentGroupId
 *
 * @package Gambio\Core\AdminAccess\Group\Models
 */
class ParentGroupId implements \Gambio\Core\AdminAccess\Group\ParentGroupId
{
    /**
     * @var int
     */
    private $id;
    
    
    /**
     * GroupId constructor.
     *
     * @param int $id
     */
    private function __construct(int $id)
    {
        $this->id = $id;
    }
    
    
    /**
     * @param int $id
     *
     * @return ParentGroupId
     */
    public static function create(int $id): ParentGroupId
    {
        Assert::greaterThan($id, 0, 'Parent group ID need to be greater than 0. Got: %s');
        
        return new self($id);
    }
    
    
    /**
     * @inheritDoc
     */
    public function value(): int
    {
        return $this->id;
    }
}