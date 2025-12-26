<?php
/* --------------------------------------------------------------
  Description.php 2020-01-10
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\ValueObjects;

use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\DescriptionInterface;

/**
 * Class Description
 * @package Gambio\CookieConsentPanel\Services\Purposes\ValueObjects
 */
class Description implements DescriptionInterface
{
    /**
     * @var string
     */
    protected $description;
    
    
    /**
     * Description constructor.
     *
     * @param array $description
     */
    public function __construct(array $description)
    {
        $this->description = $description;
    }
    
    
    /**
     * @inheritDoc
     */
    public function value(): array
    {
        return $this->description;
    }
}