<?php
/*--------------------------------------------------------------------
 ImageFactory.php 2020-06-02
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Properties\SellingUnitImages\Database\Repository\Factories;

use Gambio\Shop\Properties\SellingUnitImages\ValueObjects\PropertyImageSource;
use Gambio\Shop\SellingUnit\Database\Configurations\ShopPaths;
use Gambio\Shop\Properties\SellingUnitImages\Database\Repository\DTO\ImageDto;
use Gambio\Shop\SellingUnit\Images\Entities\Interfaces\SellingUnitImageInterface;
use Gambio\Shop\SellingUnit\Images\Entities\SellingUnitImage;
use Gambio\Shop\SellingUnit\Images\ValueObjects\ImageAlternateText;
use Gambio\Shop\SellingUnit\Images\ValueObjects\ImageNumber;
use Gambio\Shop\SellingUnit\Images\ValueObjects\ImagePath;
use Gambio\Shop\SellingUnit\Images\ValueObjects\ImageUrl;

/**
 * Class ImageFactory
 * @package Gambio\Shop\Properties\SellingUnitImages\Database\Repository\Factories
 */
class ImageFactory implements ImageFactoryInterface
{
    /**
     * @var ShopPaths
     */
    protected $paths;
    
    
    /**
     * ImageFactory constructor.
     *
     * @param ShopPaths $paths
     */
    public function __construct(ShopPaths $paths)
    {
        $this->paths = $paths;
    }
    
    
    /**
     * @inheritDoc
     */
    public function createImage(ImageDto $dto): SellingUnitImageInterface
    {
        $relativePath = $dto->relativePath();
        $absolutePath = $this->paths->absolutePath() . DIRECTORY_SEPARATOR . $relativePath;
        $url          = $relativePath;
        $imagePath    = new ImagePath($absolutePath);
        $imageUrl     = new ImageUrl($url);
        $altText      = new ImageAlternateText($dto->altText());
        $imageNumber  = new ImageNumber($dto->imageNumber());
        $infoUrl      = new ImageUrl($dto->infoPath());
        $popUpUrl     = new ImageUrl($dto->popupPath());
        $thumbnailUrl = new ImageUrl($dto->thumbnailPath());
        $galleryUrl   = new ImageUrl($dto->galleryPath());
        $source       = new PropertyImageSource;
        
        return new SellingUnitImage($imageUrl,
                                    $imagePath,
                                    $altText,
                                    $imageNumber,
                                    $infoUrl,
                                    $popUpUrl,
                                    $thumbnailUrl,
                                    $galleryUrl,
                                    $source);
    }
}