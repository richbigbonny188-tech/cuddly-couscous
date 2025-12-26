<?php
/*------------------------------------------------------------------------------
 CheckStockCriteria.php 2020-12-01
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Properties\Database\Criterias;

use InvalidArgumentException;

class CheckStockCriteria
{
    /**
     * Only defines the source of the stock check, not the stock check itself
     */
    private const GENERAL_CONFIGURATION = 0;
    private const PRODUCT_QUANTITY      = 1;
    private const COMBINATION_QUANTITY  = 2;
    
    
    /**
     * @var bool
     */
    private $stockCheck;
    
    /**
     * @var bool
     */
    private $attributesStockCheck;
    /**
     * @var bool
     */
    private $allowCheckout;
    
    
    /**
     * CheckStockCriteria constructor.
     *
     * @param bool $stockCheck
     * @param bool $attributesStockCheck
     * @param bool $allowCheckout
     */
    public function __construct(
        bool $stockCheck,
        bool $attributesStockCheck,
        bool $allowCheckout
    ) {
        $this->stockCheck           = $stockCheck;
        $this->attributesStockCheck = $attributesStockCheck;
        $this->allowCheckout        = $allowCheckout;
    }
    
    
    public function checkStockForCombinationConfiguration(int $configuration)
    {
        
        if (!$this->stockCheck) {
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
        if ($configuration === static::COMBINATION_QUANTITY
            || ($configuration === static::GENERAL_CONFIGURATION && $this->attributesStockCheck === true)) {
            return $combinationFields;
        }
        
        if ($configuration === static::PRODUCT_QUANTITY
            || ($configuration === static::GENERAL_CONFIGURATION && $this->attributesStockCheck === false)) {
            return $productField;
        }
        throw new InvalidArgumentException('Invalid Configuration');
    }

    /**
     * @return bool
     */
    public function allowCheckout(): bool
    {
        return $this->allowCheckout;
    }
}