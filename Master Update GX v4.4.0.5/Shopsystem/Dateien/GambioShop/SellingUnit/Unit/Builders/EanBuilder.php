<?php
/*------------------------------------------------------------------------------
 EanBuilder.php 2020-11-09
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\SellingUnit\Unit\Builders;

use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\EanBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Ean;

class EanBuilder implements EanBuilderInterface
{
    
    protected $eanParts = [];
    
    /**
     * @inheritDoc
     */
    public function wipeData(): EanBuilderInterface
    {
        $this->eanParts = [];
        return $this;
        
    }
    
    /**
     * @inheritDoc
     */
    public function withEanAtPos(Ean $ean, int $pos): EanBuilderInterface
    {
        $this->eanParts[$pos] = $ean;
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function build(): Ean
    {
        $data      = [];
        /** @var Ean $part */
        foreach ($this->eanParts as $part) {
            $data[] = $part->value();
        }
        $this->eanParts = [];
        return new Ean(implode('-', $data));
    }
    
}