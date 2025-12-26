<?php
/*------------------------------------------------------------------------------
 ReadService.php 2020-11-18
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnitModel\Database\Service;

use Exception;
use Gambio\Shop\Attributes\ProductModifiers\Database\ValueObjects\AttributeModifierIdentifier;
use Gambio\Shop\Attributes\SellingUnitModel\Database\Repository\DTO\AttributesModelDto;
use Gambio\Shop\Attributes\SellingUnitModel\Database\Repository\RepositoryInterface;
use Gambio\Shop\Product\ValueObjects\ProductId;

/**
 * Class ReadService
 * @package Gambio\Shop\Attributes\SellingUnitModel\Database\Service
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
    public function getAttributeModelBy(int $attributeId, int $productId): AttributesModelDto
    {
        return $this->repository->getAttributeModelBy(new AttributeModifierIdentifier($attributeId),
                                                      new ProductId($productId));
    }
}