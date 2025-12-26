<?php
/*--------------------------------------------------------------------------------------------------
    AttributeGroup.php 2020-08-26
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */
declare(strict_types=1);

namespace Gambio\Shop\Attributes\ProductModifiers\Database;

use Gambio\Shop\Attributes\ProductModifiers\Database\Interfaces\AttributeGroupInterface;
use Gambio\Shop\ProductModifiers\Groups\AbstractGroup;
use Gambio\Shop\ProductModifiers\Groups\ValueObjects\GroupStatus;

/**
 * Class AttributeGroup
 * @package Gambio\Shop\Attributes\Database\Modifiers
 */
class AttributeGroup extends AbstractGroup implements AttributeGroupInterface
{
    /**
     * @inheritDoc
     */
    public static function source(): string
    {
        return 'attribute';
    }
    
    protected function setStatus(?GroupStatus $status) {
        $this->status = new GroupStatus(true);
    }
    
   
}