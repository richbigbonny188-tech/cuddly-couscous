<?php
/*--------------------------------------------------------------------------------------------------
    InvalidQuantityGranularityException.php 2020-12-01
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2016 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\SellingUnit\Unit\Exceptions;

use Exception;
use Throwable;

/**
 * Class InvalidQuantityGranularityException
 */
class InvalidQuantityGranularityException extends Exception
{
    /**
     * @var float
     */
    private $granularity;
    
    
    /**
     * InvalidQuantityGranularityException constructor.
     *
     * @param int        $productId
     * @param float      $granularity
     * @param Exception $previous
     */
    public function __construct(int $productId, float $granularity,  ?Exception $previous)
    {
        parent::__construct('', 0, $previous);
        $this->granularity = $granularity;
    }
    
    
    /**
     * @return float
     */
    public function granularity(): float
    {
        return $this->granularity;
    }
    
}