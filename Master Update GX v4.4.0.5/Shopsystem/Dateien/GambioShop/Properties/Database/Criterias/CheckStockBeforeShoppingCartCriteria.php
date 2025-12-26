<?php
/*------------------------------------------------------------------------------
 CheckStockBeforeShoppingCartCriteria.php 2020-11-27
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\Properties\Database\Criterias;

use InvalidArgumentException;

class CheckStockBeforeShoppingCartCriteria
{
    private const GENERAL_CONFIGURATION = 0;
    private const PRODUCT_QUANTITY      = 1;
    private const COMBINATION_QUANTITY  = 2;
    
    /**
     * @var bool
     */
    private $allowCheckout;
    /**
     * @var bool
     */
    private $checkStock;
    /**
     * @var bool
     */
    private $checkStockBeforeShoppingCart;
    /**
     * @var bool
     */
    private $attributesStockCheck;
    
    
    /**
     * CheckStockBeforeShoppingCartCriteria constructor.
     *
     * @param bool $allowCheckout
     * @param bool $checkStock
     * @param bool $checkStockBeforeShoppingCart
     * @param bool $attributesStockCheck
     */
    public function __construct(
        bool $allowCheckout,
        bool $checkStock,
        bool $checkStockBeforeShoppingCart,
        bool $attributesStockCheck
    ) {
        $this->allowCheckout                = $allowCheckout;
        $this->checkStock                   = $checkStock;
        $this->checkStockBeforeShoppingCart = $checkStockBeforeShoppingCart;
        $this->attributesStockCheck         = $attributesStockCheck;
    }
    
    
    public function checkStockForCombinationConfiguration(int $configuration)
    {
        
        if ($this->allowCheckout || !$this->checkStock || !$this->checkStockBeforeShoppingCart) {
            return false;
        }
        /**
         * 0 = Use General configuration
         * 1 = Use product Quantity
         * 2 = Use combination configuration
         * 3 = Dont check stock
         */
        
        switch ($configuration) {
            case static::GENERAL_CONFIGURATION:
            case static::PRODUCT_QUANTITY:
            case static::COMBINATION_QUANTITY:
                return true;
            default:
                return false;
        }
    }
    
    
    /**
     * @param int $configuration
     * @param     $productField
     * @param     $combinationFields
     *
     * @return mixed
     */
    public function getStockSourceConfiguration(int $configuration, $productField, $combinationFields)
    {
        
        if (!$this->checkStockForCombinationConfiguration($configuration)) {
            throw new InvalidArgumentException('There is no stock check for this configuration');
        }
        return $this->getSourceConfiguration($configuration, $productField, $combinationFields);
    }
        /**
     * @param int $configuration
     * @param     $productField
     * @param     $combinationFields
     *
     * @return mixed
     */
    public function getSourceConfiguration(int $configuration, $productField, $combinationFields)
    {
        
        if ($configuration === static::COMBINATION_QUANTITY) {
            return $combinationFields;
        }
        if ($configuration === static::GENERAL_CONFIGURATION && $this->attributesStockCheck === true) {
            return $combinationFields;
        }
        if ($configuration === static::GENERAL_CONFIGURATION && $this->attributesStockCheck === false) {
            return $productField;
        }
        
        if ($configuration === static::PRODUCT_QUANTITY) {
            return $productField;
        }
        throw new InvalidArgumentException('Invalid Configuration');
    }
    
    
}