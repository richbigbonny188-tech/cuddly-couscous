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

namespace Gambio\Shop\Attributes\SellingUnit\Database\Repository;

use Gambio\Shop\Attributes\ProductModifiers\Database\ValueObjects\AttributeModifierIdentifier;
use Gambio\Shop\Attributes\SellingUnit\Database\Repository\DTO\AttributeDTO;
use Gambio\Shop\Attributes\SellingUnit\Database\Repository\Readers\ReaderInterface;
use Gambio\Shop\Product\ValueObjects\ProductId;

/**
 * Class Repository
 * @package Gambio\Shop\Attributes\SellingUnit\Database\Repository
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
    public function getAttributeBy(
        AttributeModifierIdentifier $attributeValueId,
        ProductId $productId
    ): AttributeDTO {
        return $this->reader->getAttributeBy($attributeValueId, $productId);
    }
}