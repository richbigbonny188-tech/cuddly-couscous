<?php
/*--------------------------------------------------------------------------------------------------
    ReaderService.php 2021-01-08
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

namespace Gambio\Shop\Attributes\SellingUnitQuantitiy\Database\Services;

use Doctrine\DBAL\DBALException;
use Gambio\Shop\Attributes\ProductModifiers\Database\ValueObjects\AttributeModifierIdentifier;
use Gambio\Shop\Attributes\SellingUnitQuantitiy\Database\Repository\RepositoryInterface;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\QuantityInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\ModifierQuantityInterface;
use ProductDataInterface;

class ReaderService implements ReaderServiceInterface
{
    /**
     * @var RepositoryInterface
     */
    protected $repository;
    /**
     * @var bool
     */
    private $attributeStockCheck;
    /**
     * @var bool
     */
    private $stockCheck;
    
    /**
     * @var bool
     */
    protected $downloadStockCheck;
    
    
    /**
     * ReaderService constructor.
     *
     * @param RepositoryInterface $repository
     * @param bool                $stockCheck
     * @param bool                $attributeStockCheck
     * @param bool                $downloadStockCheck
     */
    public function __construct(
        RepositoryInterface $repository,
        bool $stockCheck,
        bool $attributeStockCheck,
        bool $downloadStockCheck
    ) {
        $this->repository          = $repository;
        $this->stockCheck          = $stockCheck;
        $this->attributeStockCheck = $attributeStockCheck;
        $this->downloadStockCheck  = $downloadStockCheck;
    }
    
    
    /**
     * @inheritDoc
     * @throws DBALException
     */
    public function getQuantity(
        ProductId $productId,
        AttributeModifierIdentifier $modifierId,
        ProductDataInterface $product,
        QuantityInterface $quantity
    ): ?ModifierQuantityInterface {
        
        if ($this->attributeStockCheck && $this->stockCheck) {
            
            if ($this->downloadStockCheck === false && $this->isDownloadModifier($productId, $modifierId)) {
                
                return null;
            }
            
            return $this->repository->getQuantity($productId, $modifierId, $product, $quantity);
        }
        
        return null;
    }
    
    
    /**
     * @param ProductId                   $productId
     * @param AttributeModifierIdentifier $identifier
     *
     * @return bool
     */
    protected function isDownloadModifier(
        ProductId $productId,
        AttributeModifierIdentifier $identifier
    ): bool {
        return $this->repository->isDownloadModifier($productId, $identifier);
    }
}