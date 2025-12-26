<?php
/**
 * DeleteRepository.php 2021-02-26
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2021 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

namespace Gambio\ProductImageList\DeleteService\Repositories;

use Gambio\ProductImageList\DeleteService\Exceptions\ImageIsUsedByOtherException;
use Gambio\ProductImageList\DeleteService\Exceptions\ListIsUsedByOtherException;
use Gambio\ProductImageList\DeleteService\Interfaces\DeleteRepositoryInterface;
use Gambio\ProductImageList\DeleteService\Interfaces\DeleteServiceDeleterInterface;
use Gambio\ProductImageList\Image\ValueObjects\Id;
use Gambio\ProductImageList\ImageList\ValueObjects\ListModifierId;
use Gambio\ProductImageList\ImageList\ValueObjects\ListModifierType;
use Gambio\ProductImageList\ImageList\ValueObjects\ListId;
use InvalidArgumentException;

/**
 * Class DeleteRepository
 * @package Gambio\ProductImageList\DeleteService\Repositories
 */
class DeleteRepository implements DeleteRepositoryInterface
{
    /**
     * @var DeleteServiceDeleterInterface
     */
    private $deleter;
    
    
    /**
     * DeleteRepository constructor.
     *
     * @param DeleteServiceDeleterInterface $deleter
     */
    public function __construct(
        DeleteServiceDeleterInterface $deleter
    ) {
        $this->deleter = $deleter;
    }
    
    
    /**
     * @inheritDoc
     */
    public function deleteImageListById(ListId $id, ListModifierId $modifierId, ListModifierType $modifierType) : void
    {
        $listId = $id->value();
        if (!$this->isImageListDeletable($listId, $modifierId->value(), $modifierType->value())) {
            throw new ListIsUsedByOtherException(
                "Image list with ID {$listId} is used by other attribute or combination"
            );
        }
        $this->deleter->deleteImageListRelationsById($listId);
        $this->deleter->deleteImageTextByImageListId($listId);
        $this->deleter->deleteImageByImageListId($listId);
        $this->deleter->deleteImageListById($listId);
    }
    
    
    /**
     * @inheritDoc
     */
    public function deleteImageById(ListModifierId $modifierId, ListModifierType $modifierType, Id ...$ids) : void
    {
        $imageIds = $this->extractDtoValues(...$ids);
        foreach ($imageIds as $imageId) {
            if(!$this->isImageDeletable($imageId, $modifierId, $modifierType)) {
                throw new ImageIsUsedByOtherException(
                    "Image is being used by other attribute or combination"
                );
            }
        }
        $this->deleter->deleteImageTextByImageId(...$imageIds);
        $this->deleter->deleteImageById(...$imageIds);
    }
    
    
    /**
     * @param int              $imageId
     * @param ListModifierId   $modifierId
     * @param ListModifierType $modifierType
     *
     * @return bool
     */
    protected function isImageDeletable(int $imageId, ListModifierId $modifierId, ListModifierType $modifierType) : bool
    {
        $isImageListInUse = false;
        if($imageListId = $this->deleter->getImageImageListId($imageId)) {
            $isImageListInUse = !$this->isImageListDeletable($imageListId, $modifierId->value(), $modifierType->value());
        }
        
        return (!$isImageListInUse);
    }
    

    /**
     * @param int    $listId
     * @param int    $modifierId
     * @param string $modifierType
     *
     * @return bool
     */
    protected function isImageListDeletable(int $listId, int $modifierId, string $modifierType) : bool
    {
        [$attributeId, $combinationId] = $this->getIsImageListDeletableMethodModifierIds($modifierId, $modifierType);
        $isUsedInAttributes   = $this->deleter->getImageListUsageCountForAttributes($listId, $attributeId);
        $isUsedInCombinations = $this->deleter->getImageListUsageCountForCombinations($listId, $combinationId);
        
        return (bool)!($isUsedInAttributes || $isUsedInCombinations);
    }
    
    
    private function extractDtoValues(...$ids) : array
    {
        $values = [];
        array_walk(
            $ids,
            function (Id $id, $key) use (&$values) {
                $values[$key] = $id->value();
            }
        );
        
        return $values;
    }
    
    
    private function getIsImageListDeletableMethodModifierIds(int $modifierId, string $modifierType) : array
    {
        switch ($modifierType) {
            case('attribute'):
                return [$modifierId, null];
            case ('property'):
                return [null, $modifierId];
            default:
                throw new InvalidArgumentException("Unsupported modifier type.");
        }
    }
    
    
    /**
     * @inheritDoc
     */
    public function deleteImageListCombiAssignment(int $combis_id) : void
    {
        $this->deleter->deleteImageListCombiAssignment($combis_id);
    }
}