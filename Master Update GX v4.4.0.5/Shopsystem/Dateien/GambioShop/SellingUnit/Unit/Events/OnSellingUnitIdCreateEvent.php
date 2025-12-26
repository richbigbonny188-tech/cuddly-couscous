<?php
/*--------------------------------------------------------------------------------------------------
    OnSellingUnitIdCreateEvent.php 2020-11-02
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\SellingUnit\Unit\Events;

use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\SellingUnitIdBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\Builders\SellingUnitIdBuilder;
use Gambio\Shop\SellingUnit\Unit\Events\Interfaces\OnSellingUnitIdCreateEventInterface;

class OnSellingUnitIdCreateEvent implements OnSellingUnitIdCreateEventInterface
{
    /**
     * @var SellingUnitIdBuilderInterface
     */
    protected $builder;
    /**
     * @var string|array
     */
    private $type;
    /**
     * @var mixed
     */
    private $value;
    
    private $sets = [];
    
    
    /**
     * @inheritDoc
     */
    public function type()
    {
        return $this->type;
    }
    
    
    /**
     * @inheritDoc
     */
    public function value()
    {
        return $this->value;
    }
    
    
    /**
     * @inheritDoc
     */
    public function builder(): SellingUnitIdBuilderInterface
    {
        return $this->builder;
    }
    
    
    /**
     * OnSellingUnitIdCreateEvent constructor.
     *
     * @param string|array              $type
     * @param                           $value
     * @param SellingUnitIdBuilder|null $builder
     */
    public function __construct($type, $value, ?SellingUnitIdBuilder $builder = null)
    {
        $this->type    = $type;
        $this->value   = $value;
        $this->builder = $builder ?? new SellingUnitIdBuilder();
        if (is_array($type)) {
            foreach ($type as $key => $setName) {
                $this->sets[$setName] = $value[$key];
            }
        } else {
            $this->sets[$type] = $value;
        }
    }
    
    
    public function sets(): array
    {
        return $this->sets;
    }
}