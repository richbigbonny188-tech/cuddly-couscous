<?php
/*--------------------------------------------------------------------------------------------------
    VpeBuilder.php 2021-01-25
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2021 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Unit\Builders;

use Gambio\Shop\SellingUnit\Unit\Builders\Exceptions\UnfinishedBuildException;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Vpe;

class VpeBuilder implements Interfaces\VpeBuilderInterface
{
    
    /**
     * @var int
     */
    protected $id;
    
    /**
     * @var string
     */
    protected $name;
    
    /**
     * @var float
     */
    protected $value;
    
    
    /**
     * @inheritDoc
     */
    public static function create(): Interfaces\VpeBuilderInterface
    {
        return new static;
    }
    
    
    /**
     * @inheritDoc
     */
    public function build(): Vpe
    {
        $fields = [
            'id',
            'name',
            'value'
        ];
        
        foreach ($fields as $field) {
            
            if ($this->$field === null) {
                
                throw new UnfinishedBuildException($field . ' was not set');
            }
        }
        
        return new Vpe($this->id, $this->name, $this->value);
    }
    
    
    /**
     * @inheritDoc
     */
    public function reset(): Interfaces\VpeBuilderInterface
    {
        $this->id = $this->name = $this->value = null;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withId(int $id): self
    {
        $this->id = $id;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withName(string $name): self
    {
        $this->name = $name;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withValue(float $value): self
    {
        $this->value = $value;
        
        return $this;
    }
}
