<?php
/*------------------------------------------------------------------------------
 ReadServiceInterface.php 2020-11-18
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnitModel\Database\Service;

use Exception;
use Gambio\Shop\Attributes\SellingUnitModel\Database\Exceptions\AttributeDoesNotExistsException;
use Gambio\Shop\Attributes\SellingUnitModel\Database\Repository\DTO\AttributesModelDto;

/**
 * Interface ReadServiceInterface
 * @package Gambio\Shop\Attributes\SellingUnitModel\Database\Service
 */
interface ReadServiceInterface
{
    /**
     * @param int $attributeId
     * @param int $productId
     *
     * @return AttributesModelDto
     * @throws AttributeDoesNotExistsException
     * @throws Exception
     */
    public function getAttributeModelBy(int $attributeId, int $productId): AttributesModelDto;
}