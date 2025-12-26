<?php

/*------------------------------------------------------------------------------
 ShippingStatus.php 2020-11-25
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Properties\Properties\ValueObjects;

/**
 * Class ShippingStatus
 * @package Gambio\Shop\Properties\Properties\ValueObjects
 * @codeCoverageIgnore
 */
class ShippingStatus
{
    /**
     * @var int
     */
    private $value;
    
    
    public function __construct(int $value)
    {
        $this->value = $value;
    }
    
    
    /**
     * @return int
     */
    public function value(): int
    {
        return $this->value;
    }
    
}