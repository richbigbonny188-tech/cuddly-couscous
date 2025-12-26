<?php
/*------------------------------------------------------------------------------
 OnGetSellingUnitPriceEventListener.php 2020-10-28
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnitPrice\Listener;

use Gambio\Shop\Attributes\SellingUnitPrice\Exceptions\NoAttributeOptionValuesIdInModifierCollectionFoundException;
use Gambio\Shop\Attributes\SellingUnitPrice\Service\ReadServiceInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitPriceEventInterface;

/**
 * Class OnGetSellingUnitPriceEventListener
 * @package Gambio\Shop\Attributes\SellingUnitPrice\Listener
 */
class OnGetSellingUnitPriceEventListener
{
    /**
     * @var ReadServiceInterface
     */
    protected $readService;
    
    
    /**
     * OnGetSellingUnitPriceEventListener constructor.
     *
     * @param ReadServiceInterface $readService
     */
    public function __construct(ReadServiceInterface $readService)
    {
        $this->readService = $readService;
    }
    
    
    /**
     * @param OnGetSellingUnitPriceEventInterface $event
     */
    public function __invoke(OnGetSellingUnitPriceEventInterface $event)
    {
        
        try {
            $optionIdOptionValuesIdDtoCollection = $this->readService->getOptionIdOptionValuesId($event->modifiers(),
                                                                                                 $event->productId());
            $event->builder()->withData('attributes', $optionIdOptionValuesIdDtoCollection->toAssociativeArray());
        } catch (NoAttributeOptionValuesIdInModifierCollectionFoundException $exception) {
            unset($exception);
            
            return;
        }
    }
    
}