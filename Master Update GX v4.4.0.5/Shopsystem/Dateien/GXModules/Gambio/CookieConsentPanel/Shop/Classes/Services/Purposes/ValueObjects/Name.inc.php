<?php
/* --------------------------------------------------------------
  Name.php 2020-01-10
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\ValueObjects;

use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\NameInterface;

/**
 * Class Name
 * @package Gambio\CookieConsentPanel\Services\Purposes\ValueObjects
 */
class Name implements NameInterface
{
    
    /**
     * @var array
     */
    protected $value;
    
    
    /**
     * Name constructor.
     *
     * @param array $value
     */
    public function __construct(array $value)
    {
        $this->value = $value;
    }
    
    
    /**
     * @inheritDoc
     */
    public function value(): array
    {
        return $this->value;
    }
}