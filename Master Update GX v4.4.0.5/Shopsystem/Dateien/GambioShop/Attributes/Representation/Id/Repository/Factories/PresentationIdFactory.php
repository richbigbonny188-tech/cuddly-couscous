<?php
/*--------------------------------------------------------------------
 PresentationIdFactory.php 2020-3-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\Representation\Id\Repository\Factories;

use Gambio\Shop\Attributes\ProductModifiers\Database\ValueObjects\AttributeModifierIdentifier;
use Gambio\Shop\Attributes\Representation\Id\Repository\DTO\AttributeIdDto;
use Gambio\Shop\Attributes\Representation\ValueObjects\AttributeModifierId;
use Gambio\Shop\Attributes\SellingUnitPresentation\Entities\AttributePresentationId;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollectionInterface;
use Gambio\Shop\ProductModifiers\Modifiers\ValueObjects\ModifierIdentifierInterface;
use Gambio\Shop\Properties\SellingUnitImages\Database\Repository\DTO\CombisIdDto;
use Gambio\Shop\Properties\Representation\ValueObjects\PropertyPresentationId;

/**
 * Class PresentationIdFactory
 *
 * @package Gambio\Shop\Attributes\Representation\Id\Repository\Factories
 */
class PresentationIdFactory implements PresentationIdFactoryInterface
{

    /**
     * @inheritDoc
     */
    public function createAttributePresentationId(
        AttributeIdDto $dto,
        ModifierIdentifierCollectionInterface $collection
    ): AttributePresentationId {
        $attributeModifierId = $this->createAttributeModifierId($dto->attributeId());
        $modifierIdentifier  = $this->getModifierIdentifierByValuesId($dto->attributeValueId(), $collection);

        return new AttributePresentationId($attributeModifierId, $modifierIdentifier);
    }


    /**
     * @param int $id
     *
     * @return AttributeModifierId
     */
    protected function createAttributeModifierId(int $id): AttributeModifierId
    {
        return new AttributeModifierId($id);
    }

    /**
     * @param int $attributeOptionId
     *
     * @param ModifierIdentifierCollectionInterface $collection
     *
     * @return ModifierIdentifierInterface
     */
    protected function getModifierIdentifierByValuesId(
        int $attributeOptionId,
        ModifierIdentifierCollectionInterface $collection
    ): ModifierIdentifierInterface {
        foreach ($collection as $identifier) {

            if ($identifier instanceof AttributeModifierIdentifier && $identifier->value() === $attributeOptionId) {

                return $identifier;
            }
        }
    }


    /**
     * @inheritDoc
     */
    public function createPropertyPresentationId(CombisIdDto $dto): PropertyPresentationId
    {
        return new PropertyPresentationId($dto->combisId());
    }
}