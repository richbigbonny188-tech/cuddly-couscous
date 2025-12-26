<?php
/**
 * ReaderInterface.php 2021-01-08
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2020 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */
namespace Gambio\Shop\Attributes\SellingUnitQuantitiy\Database\Repository\Reader;


use Gambio\Shop\Attributes\SellingUnitQuantitiy\Database\Repository\DTO\AttributeInfo;

interface ReaderInterface
{

    /**
     * @param int $productId
     * @param int $modifierIdentifierId
     *
     * @return AttributeInfo
     */
    public function getQuantity(int $productId, int $modifierIdentifierId): AttributeInfo;
    
    
    /**
     * @param int $productId
     * @param int $modifierIdentifierId
     *
     * @return bool
     */
    public function isDownloadModifier(int $productId, int $modifierIdentifierId): bool;
}