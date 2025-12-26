<?php
/*--------------------------------------------------------------------
 ImagesServiceProvider.php 2020-06-02
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Core\Images;

use Gambio\Core\Application\DependencyInjection\AbstractServiceProvider;
use Gambio\Core\Images\ValueObjects\Images;
use Gambio\Core\Images\ValueObjects\ProductGalleryImages;
use Gambio\Core\Images\ValueObjects\ProductInfoImages;
use Gambio\Core\Images\ValueObjects\ProductOriginalImages;
use Gambio\Core\Images\ValueObjects\ProductPopUpImages;
use Gambio\Core\Images\ValueObjects\ProductThumbnailImages;

/**
 * Class ImagesServiceProvider
 * @package Gambio\Core\Images
 */
class ImagesServiceProvider extends AbstractServiceProvider
{
    
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            Images::class,
            ProductGalleryImages::class,
            ProductInfoImages::class,
            ProductOriginalImages::class,
            ProductPopUpImages::class,
            ProductThumbnailImages::class
        ];
    }
    
    
    public function register(): void
    {
        $this->application->registerShared(Images::class)->addArgument(DIR_WS_IMAGES);
        $this->application->registerShared(ProductGalleryImages::class)->addArgument(
            DIR_WS_IMAGES . 'product_images/gallery_images/'
        );
        $this->application->registerShared(ProductInfoImages::class)->addArgument(DIR_WS_INFO_IMAGES);
        $this->application->registerShared(ProductOriginalImages::class)->addArgument(DIR_WS_ORIGINAL_IMAGES);
        $this->application->registerShared(ProductPopUpImages::class)->addArgument(DIR_WS_POPUP_IMAGES);
        $this->application->registerShared(ProductThumbnailImages::class)->addArgument(DIR_WS_THUMBNAIL_IMAGES);
    }
}