<?php
/* --------------------------------------------------------------
  Id.php 2020-01-10
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\ValueObjects;

use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\IdInterface;

/**
 * Class Id
 * @package Gambio\CookieConsentPanel\Services\Purposes\ValueObjects
 */
class Id implements IdInterface
{
    /**
     * @var int|null
     */
    protected $id;
    
    
    /**
     * Id constructor.
     *
     * @param int|null $id
     */
    public function __construct(?int $id)
    {
        $this->id = $id;
    }
    
    
    /**
     * @inheritDoc
     */
    public function value(): ?int
    {
        return $this->id;
    }
}