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

namespace Gambio\Shop\Attributes\SellingUnit\Database\Service;

use Gambio\Shop\Attributes\SellingUnit\Database\Exceptions\AttributeDoesNotExistsException;
use Gambio\Shop\Attributes\SellingUnit\Database\Repository\DTO\AttributeDTO;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

/**
 * Interface ReadServiceInterface
 * @package Gambio\Shop\Attributes\SellingUnit\Database\Service
 */
interface ReadServiceInterface
{
    /**
     * @param int $attributeId
     * @param int $productId
     *
     * @return AttributeDTO
     * @throws AttributeDoesNotExistsException
     * @throws AttributeDoesNotExistsException
     */
    public function getAttributeModelBy(int $attributeId, int $productId): AttributeDTO;
    
    
    /**
     * @param SellingUnitId $id
     *
     * @return AttributeDTO
     * @throws AttributeDoesNotExistsException
     * @throws AttributeDoesNotExistsException
     */
    public function getVpeFor(SellingUnitId $id): ?AttributeDTO;
}