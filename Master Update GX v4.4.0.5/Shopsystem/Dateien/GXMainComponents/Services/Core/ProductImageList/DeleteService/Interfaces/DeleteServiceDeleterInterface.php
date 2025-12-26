<?php
/**
 * DeleteServiceDeleterInterface.php 2021-02-26
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2021 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

namespace Gambio\ProductImageList\DeleteService\Interfaces;

/**
 * Interface DeleteServiceDeleterInterface
 * @package Gambio\ProductImageList\DeleteService\Interfaces
 */
interface DeleteServiceDeleterInterface
{
    
    /**
     * @param int $id
     *
     * @return bool
     */
    public function deleteImageListById(int $id) : bool;
    
    
    /**
     * @param int ...$ids
     *
     * @return bool
     */
    public function deleteImageById(int ...$ids) : bool;
    
    
    /**
     * Delete multiple images by related ImageList id
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteImageByImageListId(int $id) : bool;
    
    
    /**
     * @param int ...$ids
     *
     * @return bool
     */
    public function deleteImageTextByImageId(int ...$ids) : bool;
    
    
    /**
     * @param int ...$imageListIds
     *
     * @return bool
     */
    public function deleteImageTextByImageListId(int ...$imageListIds) : bool;
    
    
    /**
     * @param int $combisId
     */
    public function deleteImageListCombiAssignment(int $combisId) : void;
    
    
    /**
     * Unlink image list from any attribute or combination
     *
     * @param int $listId
     */
    public function deleteImageListRelationsById(int $listId) : void;
    
    
    /**
     * @param int      $listId
     * @param int|null $againstId
     *
     * @return int
     */
    public function getImageListUsageCountForAttributes(int $listId, ?int $againstId) : int;
    
    
    /**
     * @param int      $listId
     * @param int|null $againstId
     *
     * @return int
     */
    public function getImageListUsageCountForCombinations(int $listId, ?int $againstId) : int;
    
    
    /**
     * @param int $imageId
     *
     * @return int|null
     */
    public function getImageImageListId(int $imageId) : ?int;
}