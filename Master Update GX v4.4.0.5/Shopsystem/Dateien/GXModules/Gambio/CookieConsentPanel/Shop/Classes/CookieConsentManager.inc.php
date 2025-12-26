<?php
/*--------------------------------------------------------------------------------------------------
    CookieManager.php 2019-12-19
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2019 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

use Gambio\CookieConsentPanel\Services\Purposes\Factories\PurposeWriteServiceFactory;

/**
 * Class CookieManager
 */
class CookieConsentManager implements CookieConsentManagerInterface
{
    /**
     * @var CookieConsentManager
     */
    private static $instance;
    /**
     * @var CookieConsentPurposeReaderServiceInterface
     */
    private $consentPurposeReaderService;
    
    
    /**
     * @var CookieConfigurationList
     */
    private $cookiesList;
    
    
    /**
     * @var CookieConfigurationList
     */
    private $vendorCookiesList;
    /**
     * @var int[]
     */
    protected $purposes = [];


    /**
     * CookieManager constructor.
     *
     * @param CookieConfigurationList                    $cookiesList
     * @param CookieConfigurationList                    $thirdPartyCookiesList
     * @param CookieConsentPurposeReaderServiceInterface $consentPurposeReaderService
     *
     * @throws Exception
     */
    public function __construct(
        CookieConfigurationList $cookiesList,
        CookieConfigurationList $thirdPartyCookiesList,
        CookieConsentPurposeReaderServiceInterface $consentPurposeReaderService
    ) {
        $this->cookiesList                 = $cookiesList;
        $this->vendorCookiesList           = $thirdPartyCookiesList;
        $this->consentPurposeReaderService = $consentPurposeReaderService;
        $this->setupCookies();
        $this->deactivateAll();
        $this->setupGXConsents();
    }
    
    
    protected function setupCookies(): void
    {
    
    }
    
    
    /**
     * @throws Exception
     */
    public function deactivateAll(): void
    {
        /**
         * @var CookieConfigurationInterface $cookieConfiguration
         */
        foreach ($this->cookiesList->getIterator() as $cookieConfiguration) {
            $cookieConfiguration->deactivate();
        }
        /**
         * @var CookieConfigurationInterface $cookieConfiguration
         */
        foreach ($this->vendorCookiesList->getIterator() as $cookieConfiguration) {
            $cookieConfiguration->deactivate();
        }
    }

    /**
     * @param int $purpose
     */
    protected function activatePurpose(int $purpose): void
    {
        $this->purposes[$purpose] = true;

    }

    /**
     * @param int $purpose
     * @return bool
     */
    public function purposeStatus(int $purpose): bool
    {
        return isset($this->purposes[$purpose]) && $this->purposes[$purpose];
    }


    /**
     *
     */
    protected function setupGXConsents(): void
    {
        
        if (isset($_COOKIE['GXConsents'])) {
            $this->activateAll();
            $gxConsents = json_decode($_COOKIE['GXConsents']);
            if (isset($gxConsents->purposeConsents)) {
                foreach ($gxConsents->purposeConsents as $purpose => $active) {
                    if (!$active) {
                        $this->deactivatePurpose((int)$purpose);
                    } else {
                        $this->activatePurpose((int)$purpose);
                    }
                }
            }
            if (isset($gxConsents->vendorConsents)) {
                foreach ($gxConsents->vendorConsents as $vendor => $active) {
                    if (!$active) {
                        $this->deactivateVendor($vendor);
                    }
                }
            }
        }
    }
    
    
    public function activateAll(): void
    {
        /**
         * @var CookieConfigurationInterface $cookieConfiguration
         */
        foreach ($this->cookiesList->getIterator() as $cookieConfiguration) {
            $cookieConfiguration->activate();
        }
        /**
         * @var CookieConfigurationInterface $cookieConfiguration
         */
        foreach ($this->vendorCookiesList->getIterator() as $cookieConfiguration) {
            $cookieConfiguration->activate();
        }
    }
    
    
    /**
     * @inheritDoc
     */
    public function deactivatePurpose(int $purpose): void
    {
        if (isset($this->purposes[$purpose]) && $this->purposes[$purpose]) {
            $this->purposes[$purpose] = false;
        }

        /**
         * @var CookieConfigurationInterface $cookieConfiguration
         */
        foreach ($this->cookiesList->getIterator() as $cookieConfiguration) {
            $cookieConfiguration->deactivatePurpose($purpose);
        }
        /**
         * @var CookieConfigurationInterface $cookieConfiguration
         */
        foreach ($this->vendorCookiesList->getIterator() as $cookieConfiguration) {
            $cookieConfiguration->deactivatePurpose($purpose);
        }
    }
    
    
    /**
     * @inheritDoc
     */
    public function deactivateVendor(int $vendorId): void
    {
        /**
         * @var CookieConfigurationInterface $cookieConfiguration
         */
        foreach ($this->cookiesList->getIterator() as $cookieConfiguration) {
            if ($cookieConfiguration->vendorId() === $vendorId) {
                $cookieConfiguration->deactivate();
            }
        }
        /**
         * @var CookieConfigurationInterface $cookieConfiguration
         */
        foreach ($this->vendorCookiesList->getIterator() as $cookieConfiguration) {
            if ($cookieConfiguration->vendorId() === $vendorId) {
                $cookieConfiguration->deactivate();
            }
        }
    }
    
    
    /**
     * @return CookieConsentManager
     */
    public static function getInstance(): CookieConsentManager
    {
        $purposeWriterFactory = new PurposeWriteServiceFactory();
        $service = $purposeWriterFactory->service();
        
        if (self::$instance === null) {
            self::$instance = MainFactory::create(__CLASS__,
                                                  new CookieConfigurationList(),
                                                  new CookieConfigurationList(),
                                                  $service);
        }
        
        return self::$instance;
    }
    
    
    /**
     * @inheritDoc
     */
    public function deactivateFeature(int $feature): void
    {
        /**
         * @var CookieConfigurationInterface $cookieConfiguration
         */
        foreach ($this->cookiesList->getIterator() as $cookieConfiguration) {
            $cookieConfiguration->deactivateFeature($feature);
        }
        /**
         * @var CookieConfigurationInterface $cookieConfiguration
         */
        foreach ($this->vendorCookiesList->getIterator() as $cookieConfiguration) {
            $cookieConfiguration->deactivateFeature($feature);
        }
    }
    
    
    /**
     * @inheritDoc
     */
    public function cookiesList(): CookieConfigurationList
    {
        return $this->cookiesList;
    }
    
    
    /**
     * @return CookieConfigurationList
     */
    public function vendorCookiesList(): CookieConfigurationList
    {
        return $this->vendorCookiesList;
    }
    
    
    /**
     * @param CookieConfigurationDTO $cookieDto
     *
     * @return CookieConfigurationInterface
     */
    protected function addCookie(CookieConfigurationDTO $cookieDto): CookieConfigurationInterface
    {
        $purposes = [];
        foreach ($cookieDto->purposes() as $purposeDto) {
            $purposes[] = $this->consentPurposeReaderService->getCookieConsentPurposeBy($purposeDto);
        }
        $cookie = new CookieConfiguration($cookieDto->vendorId(),
                                          $cookieDto->vendor(),
                                          $cookieDto->featureIds(),
                                          $cookieDto->policeUrl(),
                                          ...$purposes
        );
        $this->cookiesList->addItem($cookie);
        
        return $cookie;
    }
    
    
}