<?php
/*--------------------------------------------------------------------
 OnGetSellingUnitWeightEventListener.php 2021-01-25
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnitWeight\Database\Listener;

use Gambio\Shop\Attributes\ProductModifiers\Database\ValueObjects\AttributeModifierIdentifier;
use Gambio\Shop\Attributes\SellingUnit\Database\Exceptions\AttributeDoesNotExistsException;
use Gambio\Shop\Attributes\SellingUnit\Database\Service\ReadServiceInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitWeightEventInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnSet404HeaderEvent;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Weight;

/**
 * Class OnGetSellingUnitWeightEventListener
 * @package Gambio\Shop\Attributes\SellingUnitWeight\Database\Listener
 */
class OnGetSellingUnitWeightEventListener
{
    /**
     * @var ReadServiceInterface
     */
    protected $service;
    
    
    /**
     * OnGetSellingUnitWeightEventListener constructor.
     *
     * @param ReadServiceInterface $service
     */
    public function __construct(ReadServiceInterface $service)
    {
        $this->service = $service;
    }
    
    
    /**
     * @param OnGetSellingUnitWeightEventInterface $event
     */
    public function __invoke(OnGetSellingUnitWeightEventInterface $event)
    {
        foreach ($event->id()->modifiers() as $modifier) {
            if ($modifier instanceof AttributeModifierIdentifier) {
                try {
                    $attribute = $this->service->getAttributeModelBy($modifier->value(),
                                                                     $event->id()->productId()->value());

                    if ($attribute) {
                        if ($attribute->weightPrefix() === '+') {
                            $event->builder()->addWeight(new Weight($attribute->weight(), $event->product()->showWeight()));
                        } else {
                            $event->builder()->addWeight(new Weight($attribute->weight() * -1,
                                                                    $event->product()->showWeight()));
                        }
                    }
                } catch (AttributeDoesNotExistsException $e) {
                    // skip
                }
            }
        }
    }
}