<?php
/* --------------------------------------------------------------
  CookieConsentPanelInstallationStatus.php 2019-12-20
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2019 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

use Gambio\CookieConsentPanel\Storage\CookieConsentPanelStorage;

/**
 * Class CookieConsentPanelInstallationStatus
 */
class CookieConsentPanelInstallationStatus implements InstallationStatusInterface
{
    protected const CONFIGURATION_KEY = 'gm_configuration/MODULE_CENTER_GAMBIOCOOKIECONSENTPANEL_INSTALLED';
    
    /**
     * @var GmConfigurationInterface
     */
    protected $configuration;
    /**
     * @var CookieConsentPanelStorage
     */
    protected $storage;
    
    
    /**
     * CookieConsentPanelInstallationStatus constructor.
     *
     * @param GmConfigurationInterface $configuration
     */
    public function __construct(?GmConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
        
        if ($this->configuration !== null && $this->configuration->key() !== self::CONFIGURATION_KEY) {
    
            throw new InvalidArgumentException(__CLASS__ . ' can only be constructed with a' . GmConfigurationInterface::class
                                       . ' with the key "' . self::CONFIGURATION_KEY . '"');
        }
    }
    
    
    /**
     * @return static
     */
    public static function create(): self
    {
        /** @var GmConfigurationServiceInterface $configurationService */
        $configurationService = StaticGXCoreLoader::getService('GmConfiguration');
        
        try {
            $configuration = $configurationService->getConfigurationByKey(self::CONFIGURATION_KEY);
        }catch (GmConfigurationNotFoundException $notFoundException) {
            unset($notFoundException);
            $configuration = null;
        }
        
        return new static($configuration);
    }
    
    
    /**
     * @return CookieConsentPanelStorage
     */
    protected function storage(): CookieConsentPanelStorage
    {
        if ($this->storage === null) {
    
            $this->storage = new CookieConsentPanelStorage;
        }
        
        return $this->storage;
    }
    
    /**
     * @inheritDoc
     */
    public function isInstalled(): bool
    {
        return $this->configuration !== null && $this->configuration->value() === '1' && $this->storage()->get('active') === '1';
    }
}