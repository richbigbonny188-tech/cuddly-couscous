<?php
/* --------------------------------------------------------------
  LanguageCode.php 2020-01-10
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\ValueObjects;

/**
 * Class LanguageCode
 * @package Gambio\CookieConsentPanel\Services\Purposes\ValueObjects
 */
class LanguageCode
{
    /**
     * @var string
     */
    protected $code;
    
    
    /**
     * LanguageCode constructor.
     *
     * @param string $code
     */
    public function __construct(string $code)
    {
        $this->code = $code;
    }
    
    
    /**
     * @return string
     */
    public function value(): string
    {
        return strtolower($this->code);
    }
}