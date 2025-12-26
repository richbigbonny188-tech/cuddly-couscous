<?php
/*--------------------------------------------------------------------------------------------------
    ProductCheckStockCriteria.php 2020-12-01
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/
declare(strict_types=1);

namespace Gambio\Shop\Product\SellingUnitQuantitiy\Criteria;

class ProductCheckStockCriteria
{
    /**
     * @var bool
     */
    private $stockCheck;
    /**
     * @var bool
     */
    private $allowCheckout;
    
    
    /**
     * ProductCheckStockCriteria constructor.
     *
     * @param bool $stockCheck
     * @param bool $allowCheckout
     */
    public function __construct(
        bool $stockCheck,
        bool $allowCheckout
    ) {
        $this->stockCheck    = $stockCheck;
        $this->allowCheckout = $allowCheckout;
    }
    
    
    public function checkStock()
    {
        return $this->stockCheck;
    }
    
    
    /**
     * @return bool
     */
    public function allowCheckout(): bool
    {
        return $this->allowCheckout;
    }
}