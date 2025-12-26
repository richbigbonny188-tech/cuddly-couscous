<?php
/**
 * ProductImageListDeleteServiceInterface.php 2021-02-26
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2021 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

namespace Gambio\ProductImageList\Interfaces;

use Gambio\ProductImageList\DeleteService\Exceptions\ListIsUsedByOtherException;
use Gambio\ProductImageList\DeleteService\Exceptions\ListIsUsedForACombinationException;
use Gambio\ProductImageList\DeleteService\Exceptions\ListIsUsedForAnAttributeException;
use Gambio\ProductImageList\Image\ValueObjects\Id;
use Gambio\ProductImageList\ImageList\ValueObjects\ListModifierId;
use Gambio\ProductImageList\ImageList\ValueObjects\ListModifierType;
use Gambio\ProductImageList\ImageList\ValueObjects\ListId;

/**
 * Interface ProductImageListDeleteServiceInterface
 * @package Gambio\ProductImageList\Interfaces
 */
interface ProductImageListDeleteServiceInterface
{
    /**
     * @param ListId           $id
     * @param ListModifierId   $entityId
     * @param ListModifierType $entityType
     *
     * @throws ListIsUsedByOtherException
     */
    public function deleteImageList(ListId $id, ListModifierId $entityId, ListModifierType $entityType) : void;
    
    
    /**
     * @param ListModifierId   $modifierId
     * @param ListModifierType $modifierType
     * @param Id               ...$ids
     */
    public function deleteImage(ListModifierId $modifierId, ListModifierType $modifierType, Id ...$ids): void;
    
    
    /**
     * @param int $combis_id
     */
    public function deleteImageListCombiAssignment(int $combis_id): void;
}