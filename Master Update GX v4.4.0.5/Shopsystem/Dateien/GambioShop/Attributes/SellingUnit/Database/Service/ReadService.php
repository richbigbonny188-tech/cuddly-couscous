<?php
/*------------------------------------------------------------------------------
 ReadService.php 2020-12-10
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnit\Database\Service;

use Exception;
use Gambio\Shop\Attributes\ProductModifiers\Database\ValueObjects\AttributeModifierIdentifier;
use Gambio\Shop\Attributes\SellingUnit\Database\Repository\DTO\AttributeDTO;
use Gambio\Shop\Attributes\SellingUnit\Database\Repository\RepositoryInterface;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

/**
 * Class ReadService
 * @package Gambio\Shop\Attributes\SellingUnit\Database\Service
 */
class ReadService implements ReadServiceInterface
{
    /**
     * @var RepositoryInterface
     */
    protected $repository;
    
    
    /**
     * ReadService constructor.
     *
     * @param RepositoryInterface $repository
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
    
    
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getAttributeModelBy(int $attributeId, int $productId): AttributeDTO
    {
        return $this->repository->getAttributeBy(new AttributeModifierIdentifier($attributeId),
                                                 new ProductId($productId));
    }
    
    
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getVpeFor(SellingUnitId $id): ?AttributeDTO
    {
        $attributes = [];
        foreach ($id->modifiers() as $modifier) {
            if ($modifier instanceof AttributeModifierIdentifier) {
                $attribute = $this->repository->getAttributeBy($modifier, $id->productId());
                if($attribute->vpeId() && $attribute->vpeValue()) {
                    $attributes[] = $attribute;
                }
            }
        }
        
        usort($attributes,
            function (AttributeDTO $a, AttributeDTO $b) {
                if ($a->groupSortOrder() == $b->groupSortOrder()) {
                    return 0;
                }
                
                return ($a->groupSortOrder() < $b->groupSortOrder()) ? -1 : 1;
            });
        
        if (!empty($attributes)) {
            return end($attributes);
        }
        
        return null;
    }
}