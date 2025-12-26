<?php
/**
 * DeleteRepositoryInterface.php 2021-02-26
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2021 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

namespace Gambio\ProductImageList\DeleteService\Interfaces;

use Gambio\ProductImageList\DeleteService\Exceptions\ImageIsUsedByOtherException;
use Gambio\ProductImageList\DeleteService\Exceptions\ListIsUsedByOtherException;
use Gambio\ProductImageList\Image\ValueObjects\Id;
use Gambio\ProductImageList\ImageList\ValueObjects\ListModifierId;
use Gambio\ProductImageList\ImageList\ValueObjects\ListModifierType;
use Gambio\ProductImageList\ImageList\ValueObjects\ListId;

/**
 * Interface DeleteRepositoryInterface
 * @package Gambio\ProductImageList\DeleteService\Interfaces
 */
interface DeleteRepositoryInterface
{
    /**
     * @param ListId           $id
     * @param ListModifierId   $modifierId
     * @param ListModifierType $modifierType
     *
     * @throws ListIsUsedByOtherException
     */
    public function deleteImageListById(ListId $id, ListModifierId $modifierId, ListModifierType $modifierType) : void;
    
    
    /**
     * @param ListModifierId   $modifierId
     * @param ListModifierType $modifierType
     * @param Id               ...$ids
     *
     * @throws ImageIsUsedByOtherException
     */
    public function deleteImageById(ListModifierId $modifierId, ListModifierType $modifierType, Id ...$ids) : void;
    
    
    /**
     * @param int $combis_id
     */
    public function deleteImageListCombiAssignment(int $combis_id) : void;
}