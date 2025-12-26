<?php
/*------------------------------------------------------------------------------
 WeightBuilder.php 2020-12-09
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\SellingUnit\Unit\Builders;

use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\WeightBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Weight;

class WeightBuilder implements WeightBuilderInterface
{
    /**
     * @var ...Weight
     */
    protected $values = [];
    /**
     * @var ...Weight
     */
    private $mainWeight = [];
    /**
     * @var false
     */
    protected $show = true;
    
    
    /**
     * @param Weight $weight
     */
    public function addWeight(Weight $weight): void
    {
        $this->values[] = $weight;
    }
    
    
    /**
     * @return Weight
     */
    public function build(): ?Weight
    {
        $total = 0;
        $valid  = false;
        if (count($this->mainWeight) && end($this->mainWeight)) {
            $valid  = true;
            /** @var Weight $weight */
            $weight = end($this->mainWeight);
            if ($weight) {
                $total += $weight->value();
                $this->show = $this->show && $weight->show();
            }
            /** @var Weight $weight */
            foreach ($this->values as $weight) {
                $total += $weight->value();
                $this->show = $this->show && $weight->show();
            }
        }
        $this->reset();
        if ($valid) {
            return new Weight($total, $this->show);
        } else {
            return null;
        }
    }
    
    
    /**
     * clear all the elements of the builder
     */
    public function reset(): void
    {
        $this->values     = [];
        $this->mainWeight = [];
    }
    
    
    /**
     * @param Weight|null $weight
     * @param int         $priority
     */
    public function setMainWeight(?Weight $weight, int $priority): void
    {
        $this->mainWeight[$priority] = $weight;
        ksort($this->mainWeight);
    }
    
    
    public function hideWeight(): void
    {
        $this->show = false;
    }
}