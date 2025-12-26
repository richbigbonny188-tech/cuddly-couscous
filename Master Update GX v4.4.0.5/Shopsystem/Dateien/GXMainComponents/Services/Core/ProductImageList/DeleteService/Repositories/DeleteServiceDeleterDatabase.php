<?php
/**
 * DeleteServiceDeleterDatabase.php 2021-02-26
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2021 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

namespace Gambio\ProductImageList\DeleteService\Repositories;

use CI_DB_query_builder;
use Gambio\ProductImageList\DeleteService\Interfaces\DeleteServiceDeleterInterface;

/**
 * Class DeleteServiceDeleterDatabase
 * @package Gambio\ProductImageList\DeleteService\Repositories
 */
class DeleteServiceDeleterDatabase implements DeleteServiceDeleterInterface
{
    protected const IMAGE_LIST_TABLE_NAME             = "product_image_list";
    protected const IMAGE_LIST_IDENTIFIER             = "product_image_list_id";
    protected const IMAGE_LIST_IMAGE_TABLE_NAME       = "product_image_list_image";
    protected const IMAGE_LIST_IMAGE_IDENTIFIER       = "product_image_list_image_id";
    protected const IMAGE_LIST_IMAGE_TEXT_TABLE_NAME  = "product_image_list_image_text";
    protected const IMAGE_LIST_ATTRIBUTES_TABLE_NAME  = 'product_image_list_attribute';
    protected const IMAGE_LIST_COMBINATION_TABLE_NAME = 'product_image_list_combi';
    
    /**
     * @var CI_DB_query_builder
     */
    private $queryBuilder;
    
    
    /**
     * DeleteServiceDeleterDatabase constructor.
     *
     * @param CI_DB_query_builder $query_builder
     */
    public function __construct(
        CI_DB_query_builder $query_builder
    ) {
        $this->queryBuilder = $query_builder;
    }
    
    
    /**
     * @inheritDoc
     */
    public function deleteImageListById(int $id) : bool
    {
        return $this->queryBuilder->delete(
            self::IMAGE_LIST_TABLE_NAME,
            [
                self::IMAGE_LIST_IDENTIFIER => $id
            ]
        );
    }
    
    
    /**
     * @inheritDoc
     */
    public function deleteImageById(int ...$ids) : bool
    {
        $this->queryBuilder->where_in(self::IMAGE_LIST_IMAGE_IDENTIFIER, $ids);
        
        return $this->queryBuilder->delete(self::IMAGE_LIST_IMAGE_TABLE_NAME);
    }
    
    
    /**
     * @inheritDoc
     */
    public function deleteImageByImageListId(int $id) : bool
    {
        return $this->queryBuilder->delete(
            self::IMAGE_LIST_IMAGE_TABLE_NAME,
            [
                self::IMAGE_LIST_IDENTIFIER => $id
            ]
        );
    }
    
    
    /**
     * @inheritDoc
     */
    public function deleteImageTextByImageId(int ...$ids) : bool
    {
        $this->queryBuilder->where_in(self::IMAGE_LIST_IMAGE_IDENTIFIER, $ids);
        
        return $this->queryBuilder->delete(self::IMAGE_LIST_IMAGE_TEXT_TABLE_NAME);
    }
    
    
    /**
     * @inheritDoc
     */
    public function deleteImageTextByImageListId(int ...$imageListIds) : bool
    {
        $query = $this->concatQuery(
            [
                'DELETE FROM',
                self::IMAGE_LIST_IMAGE_TEXT_TABLE_NAME,
                'WHERE',
                self::IMAGE_LIST_IMAGE_IDENTIFIER,
                'IN (',
                'SELECT',
                self::IMAGE_LIST_IMAGE_IDENTIFIER,
                'FROM',
                self::IMAGE_LIST_IMAGE_TABLE_NAME,
                'WHERE',
                self::IMAGE_LIST_IDENTIFIER,
                'IN (',
                $this->concatQuery($imageListIds, ", "),
                ')',
                ')',
            ]
        );
        
        return $this->queryBuilder->query($query);
    }
    
    /**
     * @inheritDoc
     */
    public function deleteImageListCombiAssignment(int $combisId) : void
    {
        $this->queryBuilder->delete(
            self::IMAGE_LIST_COMBINATION_TABLE_NAME,
            'products_properties_combis_id=' . $combisId
        );
    }
    
    /**
     * @inheritDoc
     */
    public function deleteImageListRelationsById(int $listId) : void
    {
        $this->queryBuilder->delete(
            self::IMAGE_LIST_ATTRIBUTES_TABLE_NAME,
            [self::IMAGE_LIST_IDENTIFIER => $listId]
        );
        $this->queryBuilder->delete(
            self::IMAGE_LIST_COMBINATION_TABLE_NAME,
            [self::IMAGE_LIST_IDENTIFIER => $listId]
        );
    }
    
    
    /**
     * @inheritDoc
     */
    public function getImageListUsageCountForAttributes(int $listId, ?int $againstId) : int
    {
        return $this->getImageListUsageCount($listId, self::IMAGE_LIST_ATTRIBUTES_TABLE_NAME, $againstId);
    }
    
    /**
     * @inheritDoc
     */
    public function getImageListUsageCountForCombinations(int $listId, ?int $againstId) : int
    {
        return $this->getImageListUsageCount($listId, self::IMAGE_LIST_COMBINATION_TABLE_NAME, $againstId);
    }
    
    
    /**
     * @inheritDoc
     */
    public function getImageImageListId(int $imageId) : ?int
    {
        $results = $this->queryBuilder->select(self::IMAGE_LIST_IDENTIFIER)
                                      ->from(self::IMAGE_LIST_IMAGE_TABLE_NAME)
                                      ->where(self::IMAGE_LIST_IMAGE_IDENTIFIER, $imageId)
                                      ->get()
                                      ->result_array();
        if (!empty($results)) {
            return (int)array_values($results[0])[0];
        }
        
        return null;
    }
    
    
    /**
     * @param int      $listId
     * @param string   $tableName
     * @param int|null $againstId
     *
     * @return int
     */
    protected function getImageListUsageCount(int $listId, string $tableName, ?int $againstId) : int
    {
        $where = [
            self::IMAGE_LIST_IDENTIFIER => $listId
        ];
        if ($againstId && $modifierIdentifier = $this->getModifierIdentifierByType($tableName)) {
            $where["{$modifierIdentifier} !="] = $againstId;
        }
        $results = $this->queryBuilder->select()->from($tableName)->where($where)->get()->result_array();
        
        return count($results);
    }
    
    
    /**
     * @param string $type
     *
     * @return string
     */
    protected function getModifierIdentifierByType(string $type) : string
    {
        switch ($type) {
            case (self::IMAGE_LIST_ATTRIBUTES_TABLE_NAME):
                return 'products_attributes_id';
            case (self::IMAGE_LIST_COMBINATION_TABLE_NAME):
                return 'products_properties_combis_id';
            default:
                return false;
        }
    }
    
    
    
    /**
     * @param array  $array
     * @param string $glue
     *
     * @return string
     */
    private function concatQuery(array $array, string $glue = " ") : string
    {
        return implode($glue, $array);
    }
    
    
    
}