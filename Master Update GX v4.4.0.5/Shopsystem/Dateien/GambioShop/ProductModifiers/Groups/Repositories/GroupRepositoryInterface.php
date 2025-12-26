<?php
/*--------------------------------------------------------------------------------------------------
    GroupRepositoryInterface.php 2020-06-10
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\ProductModifiers\Groups\Repositories;

use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\ProductModifiers\Database\Presentation\Mappers\Exceptions\PresentationMapperNotFoundException;
use Gambio\Shop\ProductModifiers\Groups\Collections\GroupCollectionInterface;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\SellingUnit\Unit\SellingUnit;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use IdType;

/**
 * Interface GroupRepositoryInterface
 * @package Gambio\Shop\ProductModifiers\Groups\Repositories
 */
interface GroupRepositoryInterface
{
    /**
     * @param ProductId $id
     *
     * @param LanguageId $languageId
     *
     * @return GroupCollectionInterface
     */
    public function getGroupsByProduct(ProductId $id, LanguageId $languageId): GroupCollectionInterface;

    /**
     * @param SellingUnitId $id
     *
     * @return GroupCollectionInterface
     */
    public function getGroupsBySellingUnit(SellingUnitId $id): GroupCollectionInterface;

}