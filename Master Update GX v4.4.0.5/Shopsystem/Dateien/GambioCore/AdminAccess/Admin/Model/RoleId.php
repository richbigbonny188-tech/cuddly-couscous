<?php
/* --------------------------------------------------------------
   RoleId.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Admin\Model;

use Webmozart\Assert\Assert;

/**
 * Class RoleId
 *
 * @package Gambio\Core\AdminAccess\Admin\Models
 */
class RoleId implements \Gambio\Core\AdminAccess\Admin\RoleId
{
    /**
     * @var int
     */
    private $value;
    
    
    /**
     * RoleId constructor.
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
     * @return RoleId
     */
    public static function create(int $value): RoleId
    {
        Assert::greaterThan($value, 0, 'Role ID need to be greater than 0. Got: %s');
        
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