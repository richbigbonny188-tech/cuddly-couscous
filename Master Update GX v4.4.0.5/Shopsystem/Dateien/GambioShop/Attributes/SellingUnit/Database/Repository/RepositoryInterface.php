<?php
/*------------------------------------------------------------------------------
 RepositoryInterface.php 2020-11-18
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnit\Database\Repository;

use Gambio\Shop\Attributes\ProductModifiers\Database\ValueObjects\AttributeModifierIdentifier;
use Gambio\Shop\Attributes\SellingUnit\Database\Exceptions\AttributeDoesNotExistsException;
use Gambio\Shop\Attributes\SellingUnit\Database\Repository\DTO\AttributeDTO;
use Gambio\Shop\Product\ValueObjects\ProductId;

/**
 * Interface RepositoryInterface
 * @package Gambio\Shop\Attributes\SellingUnit\Database\Repository
 */
interface RepositoryInterface
{
    /**
     * @param AttributeModifierIdentifier $attributeValueId
     * @param ProductId                   $productId
     *
     * @return AttributeDTO
     * @throws AttributeDoesNotExistsException
     */
    public function getAttributeBy(
        AttributeModifierIdentifier $attributeValueId,
        ProductId $productId
    ): AttributeDTO;
}