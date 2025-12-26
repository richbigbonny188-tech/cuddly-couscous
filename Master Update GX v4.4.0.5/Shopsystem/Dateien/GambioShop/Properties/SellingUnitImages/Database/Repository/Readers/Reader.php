<?php
/*--------------------------------------------------------------------
 Reader.php 2021-01-26
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Properties\SellingUnitImages\Database\Repository\Readers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\FetchMode;
use Gambio\Core\Filesystem\Interfaces\Filesystem;
use Gambio\Core\Images\ValueObjects\ProductGalleryImages;
use Gambio\Core\Images\ValueObjects\ProductInfoImages;
use Gambio\Core\Images\ValueObjects\ProductOriginalImages;
use Gambio\Core\Images\ValueObjects\ProductPopUpImages;
use Gambio\Core\Images\ValueObjects\ProductThumbnailImages;
use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\Product\SellingUnitImage\Database\Repository\DTO\Interfaces\ImageDtoBuilderInterface;
use Gambio\Shop\Properties\SellingUnitImages\Database\Exceptions\ImageListIsEmptyException;
use Gambio\Shop\Properties\SellingUnitImages\Database\Exceptions\PropertyDoesNotHaveAnImageListException;
use Gambio\Shop\Properties\SellingUnitImages\Database\Repository\DTO\CombisIdDto;
use Gambio\Shop\Properties\SellingUnitImages\Database\Repository\DTO\ImageDto;
use Gambio\Shop\Properties\SellingUnitImages\Database\Repository\DTO\ImageDtoCollection;

/**
 * Class Reader
 * @package Gambio\Shop\Properties\SellingUnitImages\Database\Repository\Readers
 */
class Reader implements ReaderInterface
{
    protected const IMAGE_LIST_COMBI_TABLE             = 'product_image_list_combi';
    protected const IMAGE_LIST_ID_COLUMN               = 'product_image_list_id';
    protected const COMBIS_ID_COLUMN                   = 'products_properties_combis_id';
    protected const IMAGE_LIST_IMAGE_TABLE             = 'product_image_list_image';
    protected const IMAGE_LIST_IMAGE_SORT_COLUMN       = 'product_image_list_image_sort_order';
    protected const IMAGE_LIST_IMAGE_PATH_COLUMN       = 'product_image_list_image_local_path';
    protected const IMAGE_LIST_IMAGE_ID_COLUMN         = 'product_image_list_image_id';
    protected const IMAGE_LIST_TEXTS_TABLE             = 'product_image_list_image_text';
    protected const IMAGE_LIST_LANGUAGE_ID_COLUMN      = 'language_id';
    protected const IMAGE_LIST_IMAGE_TEXT_TYPE_COLUMN  = 'product_image_list_image_text_type';
    protected const IMAGE_LIST_IMAGE_TEXT_VALUE_COLUMN = 'product_image_list_image_text_value';
    protected const ORIGINAL_IMAGES_REGEXP             = '#^.*images/product_images/original_images/(.*\.)(\w+)$#';
    
    /**
     * @var Connection
     */
    protected $connection;
    /**
     * @var Filesystem
     */
    protected $filesystem;
    /**
     * @var ProductOriginalImages
     */
    protected $imageOriginalPath;
    /**
     * @var ProductInfoImages
     */
    protected $imageInfoPath;
    /**
     * @var ProductPopUpImages
     */
    protected $imagePopUpPath;
    /**
     * @var ProductThumbnailImages
     */
    protected $imageThumbnailPath;
    /**
     * @var ProductGalleryImages
     */
    protected $imageGalleryPath;


    /**
     * Reader constructor.
     *
     * @param Connection $connection
     * @param Filesystem $filesystem
     * @param ProductOriginalImages $imageOriginalPath
     * @param ProductInfoImages $imageInfoPath
     * @param ProductPopUpImages $imagePopUpPath
     * @param ProductThumbnailImages $imageThumbnailPath
     * @param ProductGalleryImages $imageGalleryPath
     */
    public function __construct(Connection $connection,
        Filesystem $filesystem,
        ProductOriginalImages $imageOriginalPath,
        ProductInfoImages $imageInfoPath,
        ProductPopUpImages $imagePopUpPath,
        ProductThumbnailImages $imageThumbnailPath,
        ProductGalleryImages $imageGalleryPath)
    {
        $this->connection = $connection;
        $this->filesystem = $filesystem;
        $this->imageOriginalPath = $imageOriginalPath;
        $this->imageInfoPath = $imageInfoPath;
        $this->imagePopUpPath = $imagePopUpPath;
        $this->imageThumbnailPath = $imageThumbnailPath;
        $this->imageGalleryPath = $imageGalleryPath;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getMainImageListImage(CombisIdDto $dto, LanguageId $languageId): ImageDto
    {
        $imageListResult = $this->imageListQueryResult($dto, $languageId);
        
        $firstResult   = $imageListResult->fetch(FetchMode::ASSOCIATIVE);
        $relativePath  = (string)$firstResult[self::IMAGE_LIST_IMAGE_PATH_COLUMN];
        $altText       = $firstResult[self::IMAGE_LIST_IMAGE_TEXT_VALUE_COLUMN];
        $number        = (int)$firstResult[self::IMAGE_LIST_IMAGE_SORT_COLUMN];
        $filename      = basename($relativePath);
        $infoPath      = $this->imageInfoPath->value().$filename;
        $thumbnailPath = $this->imageThumbnailPath->value().$filename;
        $popupPath     = $this->imagePopUpPath->value().$filename;
        $galleryPath   = $this->imageGalleryPath->value().$filename;

        return new ImageDto($relativePath, $altText, $number, $infoPath, $thumbnailPath, $popupPath, $galleryPath);
    }
    
    
    /**
     * @inheritDoc
     */
    public function getImageListImages(CombisIdDto $dto, LanguageId $languageId): ImageDtoCollection
    {
        $imageListResult = $this->imageListQueryResult($dto, $languageId)->fetchAll();
        $result          = new ImageDtoCollection;
        
        if (count($imageListResult)) {
            
            foreach ($imageListResult as $row) {
                
                $relativePath  = $row[self::IMAGE_LIST_IMAGE_PATH_COLUMN];
                $altText       = $row[self::IMAGE_LIST_IMAGE_TEXT_VALUE_COLUMN];
                $number        = (int)$row[self::IMAGE_LIST_IMAGE_SORT_COLUMN];
                $filename      = $this->getFilename($relativePath);
                $infoPath      = $this->imageInfoPath->value().$filename;
                $thumbnailPath = $this->imageThumbnailPath->value().$filename;
                $popupPath     = $this->imagePopUpPath->value().$filename;
                if($this->filesystem->has($this->imageOriginalPath->value().$filename))
                {
                    $popUpUrl = $this->imageOriginalPath->value().$filename;
                }
                $galleryPath   = $this->imageGalleryPath->value().$filename;
                $result[]      = new ImageDto($relativePath, $altText, $number, $infoPath, $thumbnailPath, $popupPath, $galleryPath);
            }
        }
        
        return $result;
    }
    
    
    /**
     * @param CombisIdDto $dto
     * @param LanguageId  $languageId
     *
     * @return Statement
     * @throws ImageListIsEmptyException
     * @throws PropertyDoesNotHaveAnImageListException
     */
    protected function imageListQueryResult(CombisIdDto $dto, LanguageId $languageId): Statement
    {
        $combisId          = $dto->combisId();
        $imageListIdResult = $this->connection->createQueryBuilder()
            ->select(self::IMAGE_LIST_ID_COLUMN)
            ->from(self::IMAGE_LIST_COMBI_TABLE)
            ->where(self::COMBIS_ID_COLUMN . ' = ' . $combisId)
            ->execute();
        
        if ($imageListIdResult->rowCount() === 0) {
            
            throw new PropertyDoesNotHaveAnImageListException('No ImageList was found for the CombisId: ' . $combisId);
        }
        
        $imageListId     = (int)$imageListIdResult->fetch(FetchMode::ASSOCIATIVE)[self::IMAGE_LIST_ID_COLUMN];
        $imageListResult = $this->connection->createQueryBuilder()
            ->select(implode(', ',
                             [
                                 self::IMAGE_LIST_IMAGE_PATH_COLUMN,
                                 self::IMAGE_LIST_IMAGE_TEXT_VALUE_COLUMN,
                                 self::IMAGE_LIST_IMAGE_SORT_COLUMN
                             ]))
            ->from(self::IMAGE_LIST_IMAGE_TABLE)
            ->leftJoin(self::IMAGE_LIST_IMAGE_TABLE,
                       self::IMAGE_LIST_TEXTS_TABLE,
                       null,
                       self::IMAGE_LIST_IMAGE_TABLE . '.' . self::IMAGE_LIST_IMAGE_ID_COLUMN . '='
                       . self::IMAGE_LIST_TEXTS_TABLE . '.' . self::IMAGE_LIST_IMAGE_ID_COLUMN)
            ->where(self::IMAGE_LIST_ID_COLUMN . ' = ' . $imageListId)
            ->andWhere(self::IMAGE_LIST_LANGUAGE_ID_COLUMN . ' = ' . $languageId->value())
            ->andWhere(self::IMAGE_LIST_TEXTS_TABLE . '.' . self::IMAGE_LIST_IMAGE_TEXT_TYPE_COLUMN . '="alt_title"')
            ->orderBy(self::IMAGE_LIST_IMAGE_SORT_COLUMN)
            ->execute();
        
        if ($imageListResult->rowCount() === 0) {
            
            throw new ImageListIsEmptyException('No Images are stored in the list with the id ' . $imageListId);
        }
        
        return $imageListResult;
    }
    
    
    /**
     * @param string $relativePath
     *
     * @return string
     */
    protected function getFilename(string $relativePath): string
    {
        if (!preg_match(self::ORIGINAL_IMAGES_REGEXP, $relativePath)) {
            
            return $relativePath;
        }
        
        return preg_replace(self::ORIGINAL_IMAGES_REGEXP,
                            '$1$2',
                            $relativePath);
    }
}