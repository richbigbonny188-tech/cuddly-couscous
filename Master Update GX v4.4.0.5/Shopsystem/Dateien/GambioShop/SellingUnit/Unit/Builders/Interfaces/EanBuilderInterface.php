<?php
/*------------------------------------------------------------------------------
 EanBuilderInterface.php 2020-11-09
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\SellingUnit\Unit\Builders\Interfaces;

use Gambio\Shop\SellingUnit\Unit\ValueObjects\Ean;

interface EanBuilderInterface
{
    /**
     * @return EanBuilderInterface
     */
    public function wipeData(): EanBuilderInterface;
    
    /**
     * @param Ean $model
     * @param int $pos
     *
     * @return EanBuilderInterface
     */
    public function withEanAtPos(Ean $model, int $pos): EanBuilderInterface;
    
    /**
     * @return Ean
     */
    public function build() : Ean;
    
    
}