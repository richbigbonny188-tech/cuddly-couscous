<?php
/*------------------------------------------------------------------------------
 ShippingInfo.php 2020-11-25
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Unit\ValueObjects;

class ShippingInfo
{
    /**
     * @var string
     */
    private $image;
    /**
     * @var string
     */
    private $description;
    /**
     * @var string
     */
    private $link;
    /**
     * @var string
     */
    private $abroadLink;
    /**
     * @var string
     */
    private $infoLinkActive;
    
    
    /**
     * ShippingInfo constructor.
     *
     * @param string $image
     * @param string $description
     * @param string $link
     * @param string $abroadLink
     * @param string $infoLinkActive
     */
    public function __construct(string $image, string $description, string $link, string $abroadLink, bool $infoLinkActive)
    {
        $this->image          = $image;
        $this->description    = $description;
        $this->link           = $link;
        $this->abroadLink     = $abroadLink;
        $this->infoLinkActive = $infoLinkActive;
    }
    
    
    /**
     * @return string
     */
    public function image(): string
    {
        return $this->image;
    }
    
    
    /**
     * @return string
     */
    public function description(): string
    {
        return $this->description;
    }
    
    
    /**
     * @return string
     */
    public function link(): string
    {
        return $this->link;
    }
    
    
    /**
     * @return string
     */
    public function abroadLink(): string
    {
        return $this->abroadLink;
    }
    
    
    /**
     * @return string
     */
    public function abroadLinkActive(): bool
    {
        return $this->infoLinkActive;
    }
}