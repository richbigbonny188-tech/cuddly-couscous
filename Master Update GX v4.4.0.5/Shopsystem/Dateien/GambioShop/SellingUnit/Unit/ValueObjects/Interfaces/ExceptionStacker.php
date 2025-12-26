<?php
/*--------------------------------------------------------------------------------------------------
    ExceptionStacker.php 2020-12-01
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces;

use Exception;

interface ExceptionStacker
{
    /**
     * @param Exception $exception
     */
    public function stackException(Exception $exception): void;
    
    
    /**
     * @return Exception|null
     */
    public function exception(): ?Exception;
    
    
    /**
     *
     * @param null $exceptionClass
     *
     * @return Exception[]
     */
    public function exceptions($exceptionClass = null);
    
    
    /**
     * @param string $exceptionName
     *
     * @return bool
     */
    public function hasException(string $exceptionName);
    
}