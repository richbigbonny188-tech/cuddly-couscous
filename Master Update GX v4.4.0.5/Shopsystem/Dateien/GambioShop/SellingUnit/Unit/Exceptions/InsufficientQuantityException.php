<?php
/*--------------------------------------------------------------------------------------------------
    InsufficientQuantityException.php 2020-12-01
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2016 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\SellingUnit\Unit\Exceptions;
use Exception;

/**
 * Class InsufficientQuantityException
 */
class InsufficientQuantityException extends Exception
{
    /**
     * @var int
     */
    private $productId;
    /**
     * @var float
     */
    private $availableQuantity;
    
    
    /**
     * InsufficientQuantityException constructor.
     *
     * @param int            $productId
     * @param float          $availableQuantity
     * @param string         $message
     */
    public function __construct(int $productId, float $availableQuantity, $message = '')
    {
        parent::__construct($message);
        $this->productId         = $productId;
        $this->availableQuantity = $availableQuantity;
    }
}