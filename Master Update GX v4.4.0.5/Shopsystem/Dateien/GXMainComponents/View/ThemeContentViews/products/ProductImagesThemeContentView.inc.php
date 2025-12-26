<?php
/*--------------------------------------------------------------------------------------------------
    ProductImagesContentView.inc.php 2020-12-23
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

use Gambio\Shop\SellingUnit\Images\Entities\Interfaces\SellingUnitImageCollectionInterface;
use Gambio\Shop\SellingUnit\Images\Entities\Interfaces\SellingUnitImageInterface;

/**
 * Class ProductImagesContentView
 */
class ProductImagesThemeContentView extends ThemeContentView
{
    /**
     * @var SellingUnitImageCollectionInterface
     */
    protected $images;

    /**
     * @var string
     */
    protected $productName = '';

    /**
     * @var string
     */
    protected $galleryHash;
    /**
     * @var int
     */
    protected $productId;

    /**
     * @var int 
     */
    protected $maxImageHeight = 0;
    
    
    /**
     * @param SellingUnitImageCollectionInterface $images
     * @return ContentViewInterface
     */
    public function setImages(SellingUnitImageCollectionInterface $images): ContentViewInterface
    {
        $this->images = $images;

        return $this;
    }

    /**
     * @param string $productName
     * @return ContentViewInterface
     */
    public function setProductName(string $productName = null): ContentViewInterface
    {
        $this->productName = $productName;

        return $this;
    }

    /**
     * @param int $productId
     * @return ContentViewInterface
     */
    public function setProductId(int $productId): ContentViewInterface
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * ProductImagesThemeContentView constructor.
     * @param bool $p_get_array
     * @param bool $p_post_array
     */
    public function __construct($p_get_array = false, $p_post_array = false)
    {
        $this->set_flat_assigns(true);

        parent::__construct($p_get_array, $p_post_array);
    }

    public function prepare_data(): void
    {
        $images = $this->sellingUnitBuildImageArray($this->images);
        $thumbnails = $this->sellingUnitBuildThumbnailArray($this->images);

        $this->set_content_data('hash', $this->get_gallery_hash($images));
        $this->set_content_data('images', $images);
        $this->set_content_data('thumbnails', $thumbnails);
        $this->set_content_data('productId', $this->productId);
    }

    /**
     * @param SellingUnitImageCollectionInterface $images
     *
     * @return array
     */
    protected function sellingUnitBuildImageArray(SellingUnitImageCollectionInterface $images): array
    {
        $imageDataArray = [];
        /**
         * @var SellingUnitImageInterface $image
         */
        foreach ($images as $image) {
            $imageMaxWidth = 369;
            $imageMaxHeight = 279;

            $infoImageSizeArray = @getimagesize($image->path());


            if (isset($infoImageSizeArray[0]) && $infoImageSizeArray[0] < $imageMaxWidth) {
                $imagePaddingLeft = round(($imageMaxWidth - $infoImageSizeArray[0]) / 2);
            } else {
                $imagePaddingLeft = 0;
            }

            if (isset($infoImageSizeArray[1]) && $infoImageSizeArray[1] < $imageMaxHeight) {
                $imagePaddingTop = round(($imageMaxHeight - $infoImageSizeArray[1]) / 2);
            } else {
                $imagePaddingTop = 0;
            }

            if ($this->maxImageHeight < $infoImageSizeArray[1]) {
                $this->maxImageHeight = $infoImageSizeArray[1];
            }

            $zoomImage = file_exists(DIR_FS_CATALOG . $image->url()->value()) ? $image->url()
                ->value() : $image->popUpUrl()->value();

            $imageDataArray[] = [
                'IMAGE' => $image->infoUrl()->value(),
                'IMAGE_ALT' => $image->alternateText()->value(),
                'IMAGE_NR' => $image->number()->value(),
                'ZOOM_IMAGE' => $zoomImage,
                'PRODUCTS_NAME' => $this->productName,
                'PADDING_LEFT' => $imagePaddingLeft,
                'PADDING_TOP' => $imagePaddingTop,
                'IMAGE_POPUP_URL' => $image->popUpUrl()->value(),
                'WIDTH' => $infoImageSizeArray[0],
                'HEIGHT' => $infoImageSizeArray[1]

            ];
        }

        return $imageDataArray;
    }

    /**
     * @param SellingUnitImageCollectionInterface $images
     *
     * @return array
     */
    protected function sellingUnitBuildThumbnailArray(SellingUnitImageCollectionInterface $images): array
    {
        $thumbnailDataArray = [];
        /**
         * @var SellingUnitImageInterface $image
         */
        foreach ($images as $image) {
            $thumbnailMaxWidth = 86;
            $thumbnailMaxHeight = 86;

            $thumbnailImageSizeArray = @getimagesize($image->gallery()->value());

            if (isset($thumbnailImageSizeArray[0]) && $thumbnailImageSizeArray[0] < $thumbnailMaxWidth) {
                $thumbnailPaddingLeft = round(($thumbnailMaxWidth - $thumbnailImageSizeArray[0]) / 2);
            } else {
                $thumbnailPaddingLeft = 0;
            }

            if (isset($thumbnailImageSizeArray[1]) && $thumbnailImageSizeArray[1] < $thumbnailMaxHeight) {
                $thumbnailPaddingTop = round(($thumbnailMaxHeight - $thumbnailImageSizeArray[1]) / 2);
            } else {
                $thumbnailPaddingTop = 0;
            }

            $zoomImage = file_exists(DIR_FS_CATALOG . $image->url()->value()) ? $image->url()
                ->value() : $image->popUpUrl()->value();

            $thumbnailDataArray[] = [
                'IMAGE' => $image->gallery()->value(),
                'IMAGE_ALT' => $image->alternateText()->value(),
                'IMAGE_NR' => $image->number()->value(),
                'ZOOM_IMAGE' => $zoomImage,
                'INFO_IMAGE' => $image->infoUrl()->value(),
                'PRODUCTS_NAME' => $this->productName,
                'PADDING_LEFT' => $thumbnailPaddingLeft,
                'PADDING_TOP' => $thumbnailPaddingTop
            ];
        }

        return $thumbnailDataArray;
    }

    /**
     * @param array $images
     * @return string
     */
    public function get_gallery_hash(array $images = null): string
    {
        $images     = $images ? : $this->sellingUnitBuildImageArray($this->images);
        $imagePaths = [];

        foreach ($images as $image) {
            $imagePaths[] = $image['IMAGE'];
        }

        return md5(serialize($imagePaths));
    }
}