<?php

/*--------------------------------------------------------------------------------------------------
    CookieConfiguration.php 2019-12-19
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2019 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

/**
 * Class CookieConfiguration
 */
class CookieConfiguration implements CookieConfigurationInterface
{
    /**
     * @var bool
     */
    protected $active = true;
    /**
     * @var array
     */
    protected $featureIds;
    protected $legIntPurposeIds = [];
    protected $policeUrl;
    /**
     * @var CookieConsentPurposeInterface[]
     */
    protected $purposes;
    /**
     * @var string
     */
    private $vendor;
    /**
     * @var int
     */
    private $vendorId;
    
    
    /**
     * CookieConfiguration constructor.
     *
     * @param int                             $vendorId
     * @param string                          $vendor
     * @param array                           $featureIds
     * @param string                          $policeUrl
     * @param CookieConsentPurposeInterface[] $purposes
     */
    public function __construct(
        int $vendorId,
        string $vendor,
        array $featureIds,
        string $policeUrl,
        CookieConsentPurposeInterface ...$purposes
    ) {
        $this->featureIds       = $featureIds;
        $this->vendor           = $vendor;
        $this->vendorId         = $vendorId;
        $this->policeUrl        = $policeUrl;
        foreach($purposes as $purpose){
            $this->purposes[] = $purpose->purposeCode();
        }
    }
    
    
    
    /**
     * @param int $feature
     *
     * @return void
     */
    public function deactivateFeature(int $feature) : void
    {
        if(in_array($feature,$this->featureIds, true)) {
            $this->deactivate();
        }
    
    }
    
    
    /**
     * @param int $purposeId
     *
     * @return void
     */
    public function deactivatePurpose(int $purposeId) : void
    {
        foreach($this->purposes as $purpose){
            if($purpose === $purposeId){
                $this->deactivate();
            }
        }
    }
    
    
    public function deactivate() : void
    {
        $this->active = false;
    }
    
    
    public function activate(): void
    {
        $this->active = true;
    }
    
    
    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $result                     = [];
        $result['id']               = $this->vendorId();
        $result['name']             = $this->vendor();
        $result['purposeIds']       = $this->purposes();
        $result['legIntPurposeIds'] = $this->legIntPurposeIds();
        $result['featureIds']       = $this->featureIds();
        $result['policyUrl']        = $this->policeUrl();
        
        return $result;
    }
    
    
    /**
     * @return int
     */
    public function vendorId(): int
    {
        return $this->vendorId;
    }
    
    
    /**
     * @return string
     */
    public function vendor(): string
    {
        return $this->vendor;
    }
    
    
    /**
     * @return array
     */
    public function purposes(): array
    {
        return $this->purposes;
    }
    
    
    /**
     * @return array
     */
    protected function legIntPurposeIds() : array
    {
        return $this->legIntPurposeIds;
    }
    
    
    /**
     * @return array
     */
    public function featureIds(): array
    {
        return $this->featureIds;
    }
    
    
    /**
     * @return string
     */
    public function policeUrl(): string
    {
        return $this->policeUrl;
    }
    
    
    /**
     * @inheritDoc
     */
    public function isActive(): bool
    {
        return $this->active;
    }
}