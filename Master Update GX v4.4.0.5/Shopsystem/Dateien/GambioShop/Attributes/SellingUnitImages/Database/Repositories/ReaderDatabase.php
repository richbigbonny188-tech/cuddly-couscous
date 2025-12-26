<?php
/*--------------------------------------------------------------------------------------------------
    ReaderDatabase.php 2021-01-26
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2021 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnitImages\Database\Repositories;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\FetchMode;
use Gambio\Shop\Attributes\SellingUnitImages\Database\Interfaces\ReaderDatabaseInterface;
use Gambio\Shop\Product\SellingUnitImage\Database\Repository\DTO\ImageDto;

class ReaderDatabase implements ReaderDatabaseInterface
{
    protected const IMAGE_LIST_ID_COLUMN               = 'product_image_list_id';
    protected const ATTRIBUTE_ID_COLUMN                = 'products_attributes_id';
    protected const IMAGE_LIST_IMAGE_TABLE             = 'product_image_list_image';
    protected const IMAGE_LIST_IMAGE_SORT_COLUMN       = 'product_image_list_image_sort_order';
    protected const IMAGE_LIST_IMAGE_PATH_COLUMN       = 'product_image_list_image_local_path';
    protected const IMAGE_LIST_IMAGE_ID_COLUMN         = 'product_image_list_image_id';
    protected const IMAGE_LIST_TEXTS_TABLE             = 'product_image_list_image_text';
    protected const IMAGE_LIST_LANGUAGE_ID_COLUMN      = 'language_id';
    protected const IMAGE_LIST_IMAGE_TEXT_TYPE_COLUMN  = 'product_image_list_image_text_type';
    protected const IMAGE_LIST_IMAGE_TEXT_VALUE_COLUMN = 'product_image_list_image_text_value';
    
    /**
     * @var Connection
     */
    private $db;
    
    
    /**
     * ReaderDatabase constructor.
     *
     * @param Connection $connection
     */
    public function __construct(
        Connection $connection
    ) {
        $this->db = $connection;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getAttributeOptionImagesByProductId(int $attributeId, int $productId, int $languageId): array
    {
        $result      = [];
        $imageListId = $this->getImageListId($attributeId, $productId);
        if ($imageListId) {
            
            $imageListResult = $this->imageListQueryResult($imageListId, $languageId);
            
            foreach ($imageListResult->fetchAll() as $row) {
                
                $relativePath = $row[self::IMAGE_LIST_IMAGE_PATH_COLUMN];
                $altText      = $row[self::IMAGE_LIST_IMAGE_TEXT_VALUE_COLUMN];
                $number       = (int)$row[self::IMAGE_LIST_IMAGE_SORT_COLUMN];
                $result[]     = new ImageDto($relativePath,
                                             $relativePath,
                                             $relativePath,
                                             $relativePath,
                                             $altText,
                                             $number,
                                             $relativePath);
            }
        }
        
        return $result;
    }
    
    
    /**
     * @param int $attributeId
     * @param int $productId
     *
     * @return int|null
     */
    protected function getImageListId(int $attributeId, int $productId): ?int
    {
        $data = $this->db->createQueryBuilder()
                         ->select('product_image_list_id')
                         ->from('product_image_list_attribute', 'pila')
                         ->innerJoin('pila',
                                     'products_attributes',
                                     'pa',
                                     'pila.products_attributes_id = pa.products_attributes_id')
                         ->where('pa.options_values_id = :options_values_id')
                         ->andWhere('pa.products_id = :products_id')
                         ->setParameter('options_values_id', $attributeId)
                         ->setParameter('products_id', $productId)
                         ->execute();
        
        if ($data->rowCount() === 0) {
            
            return null;
        }
        
        return (int)$data->fetch(FetchMode::ASSOCIATIVE)['product_image_list_id'];
    }
    
    
    /**
     * @param int $imageListId
     * @param int $languageId
     *
     * @return Statement
     */
    protected function imageListQueryResult(int $imageListId, int $languageId): Statement
    {
        
        
        return $this->db->createQueryBuilder()
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
                        ->andWhere(self::IMAGE_LIST_LANGUAGE_ID_COLUMN . ' = ' . $languageId)
                        ->andWhere(self::IMAGE_LIST_TEXTS_TABLE . '.' . self::IMAGE_LIST_IMAGE_TEXT_TYPE_COLUMN
                                   . '="alt_title"')
                        ->orderBy(self::IMAGE_LIST_IMAGE_SORT_COLUMN)
                        ->execute();
    }
}