<?php
/*--------------------------------------------------------------------------------------------------
    CookieConsentDto.php 2020-01-10
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

/**
 * Class CookieConsentDto
 */
class CookieConfigurationDTO
{
    
    /**
     * @var array
     */
    protected $featureIds = [];
    /**
     * @var CookieConsentPurposeDTO[] $purposes
     */
    protected $legIntPurposeIds = [];
    
    /**
     * @var string $policeUrl
     */
    protected $policeUrl;
    /**
     * @var CookieConsentPurposeDTO[] $purposes
     */
    protected $purposes = [];
    private   $features = [];
    
    /**
     * @var string
     */
    private $vendor;
    /**
     * @var int
     */
    private $vendorId;
    
    
    /**
     * CookieConfigurationDTO constructor.
     *
     * @param                           $vendorId
     * @param                           $vendor
     * @param                           $policeUrl
     * @param                           $features
     * @param CookieConsentPurposeDTO[] $legIntPurposeIds
     * @param CookieConsentPurposeDTO[] $purposes
     */
    public function __construct(
        $vendorId,
        $vendor,
        $policeUrl,
        $features,
        $legIntPurposeIds,
        CookieConsentPurposeDTO ...$purposes
    ) {
        $this->vendorId         = $vendorId;
        $this->vendor           = $vendor;
        $this->policeUrl        = $policeUrl;
        $this->purposes         = $purposes;
        $this->legIntPurposeIds = $legIntPurposeIds;
        $this->features         = $features;
    }
    
    
    /**
     * @return array
     */
    public function featureIds(): array
    {
        return $this->featureIds;
    }
    
    
    /**
     * @return CookieConsentPurposeDTO[]
     */
    public function legIntPurposeIds(): array
    {
        return $this->legIntPurposeIds;
    }
    
    
    /**
     * @return string
     */
    public function policeUrl(): string
    {
        return $this->policeUrl;
    }
    
    
    /**
     * @return CookieConsentPurposeDTO[]
     */
    public function purposes(): array
    {
        return $this->purposes;
    }
    
    
    /**
     * @return mixed
     */
    public function features()
    {
        return $this->features;
    }
    
    
    /**
     * @return string
     */
    public function vendor(): string
    {
        return $this->vendor;
    }
    
    
    /**
     * @return int
     */
    public function vendorId(): int
    {
        return $this->vendorId;
    }
    
    
}