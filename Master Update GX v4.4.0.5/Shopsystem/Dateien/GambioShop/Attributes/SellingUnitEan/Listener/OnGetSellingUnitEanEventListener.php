<?php
/*------------------------------------------------------------------------------
 OnGetSellingUnitEanEventListener.php 2020-11-09
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnitEan\Listener;

use Gambio\Shop\Attributes\SellingUnitEan\Service\ReadServiceInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitEanEventInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Ean;

/**
 * Class OnGetSellingUnitEanEventListener
 * @package Gambio\Shop\Attributes\SellingUnitEan\Listener
 */
class OnGetSellingUnitEanEventListener
{
    public const PRIORITY = 10000;
    /**
     * @var ReadServiceInterface
     */
    protected $service;
    
    
    public function __construct(
        ReadServiceInterface $service
    
    ) {
        $this->service = $service;
    }
    
    
    /**
     *
     * @param OnGetSellingUnitEanEventInterface $event
     *
     */
    public function __invoke(OnGetSellingUnitEanEventInterface $event)
    {
        $productId = $event->id()->productId();
        $modifiers = $event->id()->modifiers();
        if (count($modifiers)) {
            $eanCollection = $this->service->getAttributesEanValuesByProduct($productId, $modifiers);
            if ($eanCollection->count() > 0) {
                $eanDtoArray = $eanCollection->jsonSerialize();
                $position    = 10000;
                foreach ($eanDtoArray as $value) {
                    if($value->value())
                        $event->builder()->withEanAtPos(new Ean($value->value()), $position += 1000);
                }
            }
        }
    }
    
}