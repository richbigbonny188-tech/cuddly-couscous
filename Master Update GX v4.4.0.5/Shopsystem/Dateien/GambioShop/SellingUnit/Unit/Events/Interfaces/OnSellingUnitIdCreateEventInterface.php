<?php
/*------------------------------------------------------------------------------
 OnSellingUnitIdCreateEventInterface.php 2020-10-30
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\SellingUnit\Unit\Events\Interfaces;

use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\SellingUnitIdBuilderInterface;


interface OnSellingUnitIdCreateEventInterface
{
    /**
     * @return string|array
     */
    public function type();
    
    
    /**
     * @return mixed
     */
    public function value();
    
    
    /**
     * @return SellingUnitIdBuilderInterface
     */
    public function builder(): SellingUnitIdBuilderInterface;
    
    
    public function sets(): array;
    
}