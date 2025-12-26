<?php
/*------------------------------------------------------------------------------
 OnGetSellingUnitModelEventListener.php 2020-11-18
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnitModel\Database\Listener;

use Gambio\Shop\Attributes\ProductModifiers\Database\ValueObjects\AttributeModifierIdentifier;
use Gambio\Shop\Attributes\SellingUnitModel\Database\Exceptions\AttributeDoesNotExistsException;
use Gambio\Shop\Attributes\SellingUnitModel\Database\Repository\DTO\AttributesModelDto;
use Gambio\Shop\Attributes\SellingUnitModel\Database\Service\ReadServiceInterface;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\ProductModifiers\Modifiers\ValueObjects\ModifierIdentifierInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitModelEvent;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Model;

/**
 * Class OnGetSellingUnitModelEventListener
 *
 * @package Gambio\Shop\Attributes\SellingUnitModel\Database\Listener
 */
class OnGetSellingUnitModelEventListener
{
    /**
     * @var ReadServiceInterface
     */
    protected $service;
    
    
    /**
     * OnGetSellingUnitModelEventListener constructor.
     *
     * @param ReadServiceInterface $service
     */
    public function __construct(
        ReadServiceInterface $service
    ) {
        $this->service = $service;
    }
    
    
    /**
     * @param OnGetSellingUnitModelEvent $event
     *
     * @return OnGetSellingUnitModelEvent
     */
    public function __invoke(OnGetSellingUnitModelEvent $event)
    {
        $modelPos = 10000;
        foreach ($event->id()->modifiers() as $modifier) {
            if ($modifier instanceof AttributeModifierIdentifier) {
                $modelDto = $this->modelDto($modifier, $event->id()->productId());
                if ($modelDto->model()) {
                    $event->builder()->withModelAtPos(new Model($modelDto->model()), $modelPos);
                    $modelPos += 1000;
                }
            }
        }
        
        return $event;
    }
    
    
    /**
     * @param ModifierIdentifierInterface $modifier
     *
     * @param ProductId                   $productId
     *
     * @return AttributesModelDto
     */
    protected function modelDto(
        ModifierIdentifierInterface $modifier,
        ProductId $productId
    ): ?AttributesModelDto {
        try {
            $result = $this->service->getAttributeModelBy($modifier->value(), $productId->value());
        } catch (AttributeDoesNotExistsException $exception) {
            unset($exception);
            $result = new AttributesModelDto('');
        }
        
        return $result;
    }
    
}