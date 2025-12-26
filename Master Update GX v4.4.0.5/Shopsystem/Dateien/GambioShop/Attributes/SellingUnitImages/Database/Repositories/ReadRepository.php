<?php
/**
 * ReadRepository.php 2020-4-6
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2020 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnitImages\Database\Repositories;

use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\Attributes\ProductModifiers\Database\ValueObjects\AttributeModifierIdentifier;
use Gambio\Shop\Product\SellingUnitImage\Database\Repository\Factories\ImageFactoryInterface;
use Gambio\Shop\SellingUnit\Images\Entities\Interfaces\SellingUnitImageCollectionInterface;
use Gambio\Shop\SellingUnit\Images\Entities\SellingUnitImageCollection;
use Gambio\Shop\Attributes\SellingUnitImages\Database\Interfaces\ReaderDatabaseInterface;
use Gambio\Shop\Attributes\SellingUnitImages\Database\Interfaces\ReadRepositoryInterface;
use Gambio\Shop\Attributes\SellingUnitImages\ValueObjects\AttributeImageSource;

class ReadRepository implements ReadRepositoryInterface
{
    /**
     * @var ReaderDatabaseInterface
     */
    private $reader;
    
    /**
     * @var ImageFactoryInterface
     */
    private $imageFactory;
    
    
    public function __construct(
        ReaderDatabaseInterface $reader,
        ImageFactoryInterface $imageFactory
    ) {
        $this->reader       = $reader;
        $this->imageFactory = $imageFactory;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getAttributeOptionImagesByProductId(
        AttributeModifierIdentifier $attributeId,
        ProductId $productId,
        LanguageId $languageId
    ) : SellingUnitImageCollectionInterface {
        $imageList = $this->reader->getAttributeOptionImagesByProductId(
            $attributeId->value(),
            $productId->value(),
            $languageId->value()
        );
        $images    = [];
        foreach ($imageList as $imageDto) {
            $images[] = $this->imageFactory->createImage($imageDto, new AttributeImageSource);
        }
        
        return new SellingUnitImageCollection($images);
    }
}