<?php
/*--------------------------------------------------------------------------------------------------
    PropertyGroup.php 2020-08-26
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */
declare(strict_types=1);

namespace Gambio\Shop\Properties\ProductModifiers\Database;

use Gambio\Shop\Properties\ProductModifiers\Database\Interfaces\PropertyGroupInterface;
use Gambio\Shop\ProductModifiers\Groups\AbstractGroup;

/**
 * Class PropertyGroup
 * @package Gambio\Shop\Properties\ProductModifiers\Database
 */
class PropertyGroup extends AbstractGroup implements PropertyGroupInterface
{
    /**
     * @inheritDoc
     */
    public static function source(): string
    {
        return 'property';
    }
}