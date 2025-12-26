<?php
/* --------------------------------------------------------------
  CategoryDoesNotExistsException.php 2020-01-10
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\Exceptions;

use Exception;
use Throwable;

/**
 * Class CategoryDoesNotExistsException
 * @package Gambio\CookieConsentPanel\Services\Purposes\Exceptions
 */
class CategoryDoesNotExistsException extends Exception
{
    /**
     * CategoryDoesNotExistsException constructor.
     *
     * @param int            $categoryId
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(int $categoryId, $code = 0, Throwable $previous = null)
    {
        $message = 'No category with the id (' . $categoryId . ') does exists';
        parent::__construct($message, $code, $previous);
    }
}