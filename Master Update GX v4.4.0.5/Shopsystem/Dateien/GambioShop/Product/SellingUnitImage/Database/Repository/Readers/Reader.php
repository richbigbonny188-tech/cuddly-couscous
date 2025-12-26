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

namespace Gambio\Shop\Product\SellingUnitImage\Database\Repository\Readers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Gambio\Core\Filesystem\Interfaces\Filesystem;
use Gambio\Core\Images\ValueObjects\ProductGalleryImages;
use Gambio\Core\Images\ValueObjects\ProductInfoImages;
use Gambio\Core\Images\ValueObjects\ProductOriginalImages;
use Gambio\Core\Images\ValueObjects\ProductPopUpImages;
use Gambio\Core\Images\ValueObjects\ProductThumbnailImages;
use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\Product\SellingUnitImage\Database\Exceptions\ProductDoesNotHaveAnImageException;
use Gambio\Shop\Product\SellingUnitImage\Database\Repository\DTO\ImageDto;
use Gambio\Shop\Product\SellingUnitImage\Database\Repository\DTO\ImageDtoCollection;
use Gambio\Shop\Product\SellingUnitImage\Database\Repository\DTO\Interfaces\ImageDtoBuilderInterface;
use Gambio\Shop\Product\ValueObjects\ProductId;

/**
 * Class Reader
 * @package Gambio\Shop\SellingUnit\ProductInformation\Services\ProductImage\Repositories
 */
class Reader implements ReaderInterface
{
    protected $imageOriginalPath;
    protected $imageInfoPath;
    protected $imagePopUpPath;
    protected $imageThumbnailPath;
    protected $imageGalleryPath;

    protected const PRODUCTS_IMAGES_TABLE         = 'products_images';
    protected const PRODUCTS_ID_COLUMN            = 'products_images.products_id';
    protected const PRODUCTS_IMAGE_NAME_COLUMN    = 'products_images.image_name';
    protected const PRODUCTS_IMAGE_SORTING_COLUMN = 'products_images.image_nr';
    protected const PRODUCTS_IMAGE_ACTIVE_COLUMN  = 'products_images.gm_show_image';
    
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var ImageDtoBuilderInterface
     */
    private $builder;


    /**
     * ProductImageReader constructor.
     *
     * @param Connection $connection
     * @param Filesystem $filesystem
     * @param ImageDtoBuilderInterface $builder
     * @param ProductOriginalImages $imageOriginalPath
     * @param ProductInfoImages $imageInfoPath
     * @param ProductPopUpImages $imagePopUpPath
     * @param ProductThumbnailImages $imageThumbnailPath
     * @param ProductGalleryImages $imageGalleryPath
     */
    public function __construct(
        Connection $connection,
        Filesystem $filesystem,
        ImageDtoBuilderInterface $builder,
        ProductOriginalImages $imageOriginalPath,
        ProductInfoImages $imageInfoPath,
        ProductPopUpImages $imagePopUpPath,
        ProductThumbnailImages $imageThumbnailPath,
        ProductGalleryImages $imageGalleryPath
    )
    {
        $this->connection = $connection;
        $this->filesystem = $filesystem;
        $this->builder = $builder;
        $this->imageOriginalPath = $imageOriginalPath->value();
        $this->imageInfoPath = $imageInfoPath->value();
        $this->imagePopUpPath = $imagePopUpPath->value();
        $this->imageThumbnailPath = $imageThumbnailPath->value();
        $this->imageGalleryPath = $imageGalleryPath->value();
    }
    
    
    /**
     * @inheritDoc
     */
    public function getMainProductImage(ProductId $id, LanguageId $languageId): ImageDto
    {
        $productId = $id->value();
        $result    = $this->connection->createQueryBuilder()
                                      ->select('p.products_image, 0 as image_nr, pd.gm_alt_text')
                                      ->from('products', 'p')
                                      ->leftJoin('p',
                                                 'products_description',
                                                 'pd',
                                                 'pd.products_id = p.products_id and pd.language_id = '
                                                 . $languageId->value())
                                      ->where('p.products_id = ' . $id->value())
                                      ->andWhere("trim(coalesce(products_image, '')) <> ''")
                                      ->andWhere('gm_show_image = 1')
                                      ->execute();
        
        if ($result->rowCount() !== 0) {
            
            $row = $result->fetch(FetchMode::ASSOCIATIVE);

            $popUpUrl = $this->imagePopUpPath . $row['products_image'];
            if($this->filesystem->has($this->imageOriginalPath.$row['products_image']))
            {
                $popUpUrl = $this->imageOriginalPath.$row['products_image'];
            }
            
            return $this->builder->withRelativePath($this->imageOriginalPath.$row['products_image'])
                                 ->withInfoUrl($this->imageInfoPath . $row['products_image'])
                                 ->withPopUpUrl($popUpUrl)
                                 ->withThumbnailUrl($this->imageThumbnailPath . $row['products_image'])
                                 ->withAlternativeText($row['gm_alt_text'] ?? '')
                                 ->withGalleryUrl($this->imageGalleryPath . $row['products_image'])
                                 ->withNumber((int)$row['image_nr'])
                                 ->build();
        }
        
        throw new ProductDoesNotHaveAnImageException('Product:' . $productId . ' has no active Image in');
    }
    
    
    /**
     * @inheritDoc
     */
    public function getProductImages(ProductId $id, LanguageId $languageId): ImageDtoCollection
    {
        $result      = new ImageDtoCollection();
        $productId   = $id->value();
        $queryResult = $this->connection->createQueryBuilder()
                                        ->select('products_images.image_name,products_images.image_nr, alt.gm_alt_text')
                                        ->from(self::PRODUCTS_IMAGES_TABLE)
                                        ->leftJoin(self::PRODUCTS_IMAGES_TABLE,
                                                   'gm_prd_img_alt',
                                                   'alt',
                                                   'alt.image_id = products_images.image_id
                                                  and alt.language_id = ' . $languageId->value())
                                        ->where(self::PRODUCTS_ID_COLUMN . ' = ' . $id->value())
                                        ->andWhere(self::PRODUCTS_IMAGE_ACTIVE_COLUMN . ' = 1')
                                        ->orderBy(self::PRODUCTS_IMAGE_SORTING_COLUMN)
                                        ->execute();
        
        foreach ($queryResult as $row) {
            $popUpUrl = $this->imagePopUpPath . $row['image_name'];
            if($this->filesystem->has($this->imageOriginalPath.$row['image_name']))
            {
                $popUpUrl = $this->imageOriginalPath.$row['image_name'];
            }

            $result[] =  $this->builder->withRelativePath($this->imageOriginalPath.$row['image_name'])
                ->withInfoUrl($this->imageInfoPath . $row['image_name'])
                ->withPopUpUrl($popUpUrl)
                ->withThumbnailUrl($this->imageThumbnailPath . $row['image_name'])
                ->withAlternativeText($row['gm_alt_text'] ?? '')
                ->withGalleryUrl($this->imageGalleryPath . $row['image_name'])
                ->withNumber((int)$row['image_nr'])
                ->build();

        }
        
        if (!count($result)) {
            throw new ProductDoesNotHaveAnImageException('Product:' . $productId . ' has no active Image in');
        }
        
        return $result;
    }
}