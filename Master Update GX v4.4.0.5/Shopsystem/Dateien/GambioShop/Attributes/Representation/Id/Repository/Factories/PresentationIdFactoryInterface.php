<?php
/*--------------------------------------------------------------------
 PresentationIdFactoryInterface.php 2020-3-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\Representation\Id\Repository\Factories;

use Gambio\Shop\Attributes\Representation\Id\Repository\DTO\AttributeIdDto;
use Gambio\Shop\Attributes\SellingUnitPresentation\Entities\AttributePresentationId;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollectionInterface;
use Gambio\Shop\Properties\SellingUnitImages\Database\Repository\DTO\CombisIdDto;
use Gambio\Shop\Properties\Representation\ValueObjects\PropertyPresentationId;

/**
 * Interface PresentationIdFactoryInterface
 *
 * @package Gambio\Shop\Attributes\Representation\Id\Repository\Factories
 */
interface PresentationIdFactoryInterface
{
    /**
     * @param AttributeIdDto $dto
     *
     * @param ModifierIdentifierCollectionInterface $collection
     *
     * @return AttributePresentationId
     */
    public function createAttributePresentationId(
        AttributeIdDto $dto,
        ModifierIdentifierCollectionInterface $collection
    ): AttributePresentationId;


    /**
     * @param CombisIdDto $dto
     *
     * @return PropertyPresentationId
     */
    public function createPropertyPresentationId(CombisIdDto $dto): PropertyPresentationId;
}