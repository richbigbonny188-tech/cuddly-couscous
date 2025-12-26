<?php
/* --------------------------------------------------------------
  PurposeNameCanNotBeEmptyException.php 2020-01-30
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\CookieConsentPanel\Services\Purposes\Exceptions;

use Exception;
use Throwable;

/**
 * Class PurposeNameCanNotBeEmptyException
 * @package Gambio\CookieConsentPanel\Services\Purposes\Exceptions
 */
class PurposeNameCanNotBeEmptyException extends Exception
{
    /**
     * PurposeNameCanNotBeEmptyException constructor.
     *
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct('Purpose names can not be empty', $code, $previous);
    }
}