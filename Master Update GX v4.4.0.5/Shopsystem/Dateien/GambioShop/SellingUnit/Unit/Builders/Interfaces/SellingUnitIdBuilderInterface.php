<?php
/*--------------------------------------------------------------------------------------------------
    SellingUnitIdBuilderInterface.php 2020-02-18
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\SellingUnit\Unit\Builders\Interfaces;

use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\ProductModifiers\Modifiers\ValueObjects\ModifierIdentifierInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

interface SellingUnitIdBuilderInterface
{
    /**
     * @param ProductId $id
     *
     * @return mixed
     */
    public function withProductId(ProductId $id): SellingUnitIdBuilderInterface;
    
    
    /**
     * @param ModifierIdentifierInterface $id
     *
     * @return SellingUnitIdBuilderInterface
     */
    public function withModifierId(ModifierIdentifierInterface $id): SellingUnitIdBuilderInterface;
    
    
    /**
     * @param LanguageId $id
     *
     * @return SellingUnitIdBuilderInterface
     */
    public function withLanguageId(LanguageId $id): SellingUnitIdBuilderInterface;
    
    
    /**
     * @return SellingUnitId
     * @throws \Throwable
     
     */
    public function build(): SellingUnitId;
    
}