<?php
/*--------------------------------------------------------------------------------------------------
    ConfigurationAdapter.php 2020-08-13
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace GXModules\Gambio\StyleEdit\Adapters;

class ConfigurationAdapter implements Interfaces\ConfigurationAdapterInterface
{
    
    /**
     * @var \GmConfigurationServiceInterface
     */
    private $configurationService;
    
    
    public function __construct()
    {
        $this->configurationService = \StaticGXCoreLoader::getService('GmConfiguration');
    }
    
    
    public static function create()
    {
        return new self();
    }
    
    /**
     * @inheritDoc
     */
    public function get(string $key)
    {
        return $this->configurationService->getConfigurationByKey($key);
    }
    
    
    /**
     * @inheritDoc
     */
    public function set(string $key, $value): void
    {
        $configuration = $this->get($key);
        $configuration->setValue($value);
        $this->configurationService->updateGmConfiguration($configuration);
    }
}