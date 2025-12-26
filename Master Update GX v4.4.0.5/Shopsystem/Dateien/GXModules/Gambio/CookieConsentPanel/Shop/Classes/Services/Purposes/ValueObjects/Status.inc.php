<?php
/* --------------------------------------------------------------
  Status.php 2020-01-10
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\ValueObjects;

use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\StatusInterface;

/**
 * Class Status
 * @package Gambio\CookieConsentPanel\Services\Purposes\ValueObjects
 */
class Status implements StatusInterface
{
    /**
     * @var bool
     */
    protected $active;
    
    
    /**
     * Status constructor.
     *
     * @param bool $active
     */
    public function __construct(bool $active)
    {
        $this->active = $active;
    }
    
    
    /**
     * @inheritDoc
     */
    public function isActive(): bool
    {
        return $this->active;
    }
}