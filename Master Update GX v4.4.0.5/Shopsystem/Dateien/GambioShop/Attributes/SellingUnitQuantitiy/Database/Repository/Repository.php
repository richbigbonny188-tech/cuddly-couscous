<?php
/*--------------------------------------------------------------------------------------------------
    Repository.php 2021-01-08
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

namespace Gambio\Shop\Attributes\SellingUnitQuantitiy\Database\Repository;

use Doctrine\DBAL\DBALException;
use Gambio\Shop\Attributes\ProductModifiers\Database\ValueObjects\AttributeModifierIdentifier;
use Gambio\Shop\Attributes\SellingUnitQuantitiy\Database\Repository\Reader\ReaderInterface;
use Gambio\Shop\Attributes\SellingUnitQuantitiy\ValueObjects\AttributeQuantity;
use Gambio\Shop\Attributes\SellingUnitQuantitiy\ValueObjects\AttributeScopedReservedQuantity;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\QuantityInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\ScopedQuantityInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\ModifierQuantityInterface;
use ProductDataInterface;

/**
 * Class Repository
 * @package Gambio\Shop\Attributes\SellingUnitQuantitiy\Database\Repository
 */
class Repository implements RepositoryInterface
{
    /**
     * @var ReaderInterface
     */
    protected $reader;
    
    
    /**
     * Repository constructor.
     *
     * @param ReaderInterface $reader
     */
    public function __construct(ReaderInterface $reader)
    {
        $this->reader = $reader;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getQuantity(
        ProductId $productId,
        AttributeModifierIdentifier $modifierId,
        ProductDataInterface $product,
        QuantityInterface $quantity
    ): ?ModifierQuantityInterface {
        $dto               = $this->reader->getQuantity($productId->value(), $modifierId->value());
        $availableQuantity = $dto->value();
        
        if ($quantity instanceof ScopedQuantityInterface) {
            /** @var ScopedQuantityInterface $quantity */
            $availableQuantity -= $quantity->scope()->quantityForModifier($productId, $modifierId);
        }
        
        return new AttributeQuantity($availableQuantity, $product->measureUnit(), $modifierId);
    }
    
    
    /**
     * @param ProductId                   $productId
     * @param AttributeModifierIdentifier $identifier
     *
     * @return bool
     */
    public function isDownloadModifier(
        ProductId $productId,
        AttributeModifierIdentifier $identifier
    ): bool {
        return $this->reader->isDownloadModifier($productId->value(), $identifier->value());
    }
}