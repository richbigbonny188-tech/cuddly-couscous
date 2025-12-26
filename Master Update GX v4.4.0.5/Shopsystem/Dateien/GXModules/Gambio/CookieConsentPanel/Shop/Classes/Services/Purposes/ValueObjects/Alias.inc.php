<?php
/* --------------------------------------------------------------
  Alias.php 2020-01-10
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\ValueObjects;

use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\AliasInterface;

/**
 * Class Alias
 * @package Gambio\CookieConsentPanel\Services\Purposes\ValueObjects
 */
class Alias implements AliasInterface
{
    /**
     * @var string|null
     */
    protected $alias;
    
    
    /**
     * Alias constructor.
     *
     * @param string|null $alias
     */
    public function __construct(?string $alias)
    {
        $this->alias = $alias;
    }
    
    
    /**
     * @inheritDoc
     */
    public function value(): ?string
    {
        return $this->alias;
    }
}