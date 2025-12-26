<?php
/*--------------------------------------------------------------------------------------------------
    RepositoryInterface.php 2021-01-08
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnitQuantitiy\Database\Repository;


use Gambio\Shop\Attributes\ProductModifiers\Database\ValueObjects\AttributeModifierIdentifier;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\QuantityInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\ModifierQuantityInterface;
use ProductDataInterface;

interface RepositoryInterface
{
    /**
     * @param ProductId $productId
     * @param AttributeModifierIdentifier $modifierId
     * @param ProductDataInterface $product
     * @param QuantityInterface $quantity
     * @return mixed
     */
    public function getQuantity(
        ProductId $productId,
        AttributeModifierIdentifier $modifierId,
        ProductDataInterface $product,
        QuantityInterface $quantity
    ): ?ModifierQuantityInterface;
    
    
    /**
     * @param ProductId                   $productId
     * @param AttributeModifierIdentifier $identifier
     *
     * @return bool
     */
    public function isDownloadModifier(
        ProductId $productId,
        AttributeModifierIdentifier $identifier
    ): bool;
}