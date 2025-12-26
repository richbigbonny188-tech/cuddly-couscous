<?php
/*--------------------------------------------------------------------------------------------------
    OnSellingUnitIdCreateListener.php 2021-01-25
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2021 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\Attributes\SellingUnit\Database\Listener;

use Gambio\Shop\Attributes\ProductModifiers\Database\ValueObjects\AttributeModifierIdentifier;
use Gambio\Shop\Attributes\SellingUnit\Database\Exceptions\AttributeDoesNotExistsException;
use Gambio\Shop\Attributes\SellingUnit\Database\Service\ReadServiceInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnSet404HeaderEvent;
use Gambio\Shop\SellingUnit\Unit\Events\Interfaces\OnSellingUnitIdCreateEventInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class OnSellingUnitIdCreateListener
{
    /**
     * @var ReadServiceInterface
     */
    protected $service;
    
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;
    
    
    /**
     * OnSellingUnitIdCreateListener constructor.
     *
     * @param ReadServiceInterface     $service
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        ReadServiceInterface $service,
        EventDispatcherInterface $dispatcher
    ) {
        $this->service    = $service;
        $this->dispatcher = $dispatcher;
    }
    
    
    /**
     * @param OnSellingUnitIdCreateEventInterface $event
     *
     * @return OnSellingUnitIdCreateEventInterface
     */
    public function __invoke(OnSellingUnitIdCreateEventInterface $event): OnSellingUnitIdCreateEventInterface
    {
        foreach ($event->sets() as $type => $value) {
            if ($type === 'product' || $type === 'info') {
                $productId = (int)preg_replace('#^p?(\d+).*$#', '$1', $value);
                $regex = $type === 'product' ? "/[\d]+((?:\{[\d]+\}[\d]+)+)/" : "/p[\d]+((?:\{[\d]+\}[\d]+)+)/";
                preg_match($regex, $value, $t_extract);
                if (isset($t_extract[1])) {
                    $attributes = $this->parseValue($t_extract[1]);
                    foreach ($attributes as $attribute) {
                        if ($attribute[1] !== '0') {
                            
                            $identifier = new AttributeModifierIdentifier($attribute[1]);
                            $this->validateAttribute($identifier, $productId);
                            
                            $event->builder()->withModifierId($identifier);
                        }
                    }
                }
            }
        }

        return $event;
    }
    
    
    /**
     * @param string $value
     *
     * @return array
     */
    protected function parseValue(string $value): array
    {
        $values = explode('{', $value);
        array_shift($values);
        
        return array_map(function ($value) {
            return explode('}', $value);
        },
            $values);
    }
    
    
    /**
     * @param AttributeModifierIdentifier $identifier
     * @param int                         $productId
     */
    protected function validateAttribute(AttributeModifierIdentifier $identifier, int $productId): void
    {
        try {
            $this->service->getAttributeModelBy($identifier->value(), $productId);
        } catch (AttributeDoesNotExistsException $exception) {
            $this->dispatcher->dispatch(new OnSet404HeaderEvent);
        }
    }
}
