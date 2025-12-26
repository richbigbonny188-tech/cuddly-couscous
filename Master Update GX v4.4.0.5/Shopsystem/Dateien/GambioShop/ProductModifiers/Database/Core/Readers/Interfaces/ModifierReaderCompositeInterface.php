<?php
/*--------------------------------------------------------------------------------------------------
    ModifierReaderCompositeInterface.php 2020-06-10
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\ProductModifiers\Database\Core\Readers\Interfaces;

use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Modifiers\ModifierDTOCollectionInterface;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use IdType;

/**
 * Interface ModifierReaderCompositeInterface
 * @package Gambio\Shop\ProductModifiers\Database\Core\Readers\Interfaces
 */
interface ModifierReaderCompositeInterface
{

    /**
     * @param SellingUnitId $id
     *
     * @return mixed
     */
    public function getModifierBySellingUnit(SellingUnitId $id): ModifierDTOCollectionInterface;
}