<?php
/*------------------------------------------------------------------------------
 Repository.php 2020-11-18
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnitModel\Database\Repository;

use Gambio\Shop\Attributes\ProductModifiers\Database\ValueObjects\AttributeModifierIdentifier;
use Gambio\Shop\Attributes\Representation\ValueObjects\AttributeModifierId;
use Gambio\Shop\Attributes\SellingUnitModel\Database\Repository\DTO\AttributesModelDto;
use Gambio\Shop\Attributes\SellingUnitModel\Database\Repository\Readers\ReaderInterface;
use Gambio\Shop\Product\ValueObjects\ProductId;

/**
 * Class Repository
 * @package Gambio\Shop\Attributes\SellingUnitModel\Database\Repository
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
    public function getAttributeModelBy(AttributeModifierIdentifier $attributeValueId, ProductId $productId): AttributesModelDto
    {
        return $this->reader->getAttributeModelBy($attributeValueId, $productId);
    }
}