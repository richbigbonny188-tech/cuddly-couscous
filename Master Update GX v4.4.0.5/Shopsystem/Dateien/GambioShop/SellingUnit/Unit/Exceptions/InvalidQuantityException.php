<?php
/*--------------------------------------------------------------------------------------------------
    InvalidQuantityException.php 2020-12-01
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2016 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */
declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Unit\Exceptions;

use Exception;

class InvalidQuantityException extends Exception
{
    public function __construct(string $message = '')
    {
        parent::__construct($message, 0, null);
    }
    
}