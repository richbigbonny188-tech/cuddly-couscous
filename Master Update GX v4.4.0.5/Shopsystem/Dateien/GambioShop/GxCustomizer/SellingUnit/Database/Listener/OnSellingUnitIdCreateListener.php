<?php
/*--------------------------------------------------------------------------------------------------
    OnSellingUnitIdCreateListener.php 2020-06-10
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\GxCustomizer\SellingUnit\Database\Listener;

use Gambio\Shop\GxCustomizer\ProductModifiers\Database\ValueObjects\CustomizerModifierIdentifier;
use Gambio\Shop\SellingUnit\Unit\Events\Interfaces\OnSellingUnitIdCreateEventInterface;

class OnSellingUnitIdCreateListener
{
    /**
     * @param OnSellingUnitIdCreateEventInterface $event
     *
     * @return OnSellingUnitIdCreateEventInterface
     */
    public function __invoke(OnSellingUnitIdCreateEventInterface $event): OnSellingUnitIdCreateEventInterface
    {
        foreach ($event->sets() as $type => $value) {
            if ($type === 'product' || $type === 'info') {
                $regex = $type
                         === 'product' ? "/[\d]+((?:\{[\d]+\}[\d]+)+)/" : "/p[\d]+((?:\{[\d]+\}[\d]+)+)/";
                preg_match($regex, $value, $t_extract);
                if (isset($t_extract[1])) {
                    $attributes = $this->parseValue($t_extract[1]);
                    foreach ($attributes as $attribute) {
                        if ($attribute[1] === '0') {
                            $event->builder()->withModifierId(new CustomizerModifierIdentifier($attribute[0]));
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
    
}