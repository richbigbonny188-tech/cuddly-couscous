<?php
/* --------------------------------------------------------------
  Deletable.php 2020-01-10
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\ValueObjects;

use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\DeletableInterface;

/**
 * Class Deletable
 * @package Gambio\CookieConsentPanel\Services\Purposes\ValueObjects
 */
class Deletable implements DeletableInterface
{
    /**
     * @var bool
     */
    protected $deletable;
    
    
    /**
     * Deletable constructor.
     *
     * @param bool $deletable
     */
    public function __construct(bool $deletable)
    {
        $this->deletable = $deletable;
    }
    
    
    /**
     * @inheritDoc
     */
    public function value(): bool
    {
        return $this->deletable;
    }
}