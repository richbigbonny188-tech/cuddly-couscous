<?php
/*--------------------------------------------------------------------------------------------------
    DiscountAllowed.php 2021-03-29
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2021 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Unit\ValueObjects;

/**
 * Class DiscountAllowed
 * @package Gambio\Shop\SellingUnit\Unit\ValueObjects
 */
class DiscountAllowed
{
    /**
     * @var float
     */
    private $discount;
    
    
    public function __construct(float $discount)
    {
        $this->discount = $discount;
    }
    
    
    /**
     * @return float
     */
    public function value(): float
    {
        return $this->discount;
    }
}
