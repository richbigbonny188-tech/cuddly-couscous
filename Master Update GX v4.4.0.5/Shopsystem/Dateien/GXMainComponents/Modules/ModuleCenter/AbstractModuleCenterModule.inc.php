<?php
/* --------------------------------------------------------------
  AbstractModuleCenterModule.inc.php 2021-01-22
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2021 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------
*/

use Gambio\Admin\Application\GambioAdminBootstrapper;
use Gambio\Core\Application\Application;
use Gambio\Core\Application\DependencyInjection\LeagueContainer;
use Gambio\Core\Configuration\Compatibility\ModulesConfigurationRepository;
use Gambio\Admin\Layout\Menu\AdminMenuService;

/**
 * Class AbstractModuleCenterModule
 * @implements  ModuleCenterModuleInterface
 * @category    System
 * @package     Modules
 */
abstract class AbstractModuleCenterModule implements ModuleCenterModuleInterface
{
    private const LEGACY_PREFIX = 'gm_configuration/';
    
    /**
     * @var LanguageTextManager $languageTextManager
     */
    protected $languageTextManager;
    
    /**
     * @var CI_DB_query_builder
     */
    protected $db;
    
    /**
     * @var null|bool $isInstalled
     */
    protected $isInstalled = null;
    
    /**
     * @var string $name
     */
    protected $name = '';
    
    /**
     * @var string $title
     */
    protected $title = '';
    
    /**
     * @var string $description
     */
    protected $description = '';
    
    /**
     * @var int $sortOrder
     */
    protected $sortOrder = 100000;
    
    /**
     * @var CacheControl $cacheControl
     */
    protected $cacheControl;
    
    
    /**
     * @param LanguageTextManager $languageTextManager
     * @param CI_DB_query_builder $db
     * @param CacheControl        $cacheControl
     */
    public function __construct(
        LanguageTextManager $languageTextManager,
        CI_DB_query_builder $db,
        CacheControl $cacheControl
    ) {
        $this->languageTextManager = $languageTextManager;
        $this->db                  = $db;
        $this->cacheControl        = $cacheControl;
        
        $this->_init();
    }
    
    
    /**
     * Initialize the module e.g. set title, description, sort order etc.
     *
     * Function will be called in the constructor
     */
    abstract protected function _init();
    
    
    /**
     * Set module name
     */
    protected function _setModuleName()
    {
        $moduleName = get_called_class();
        $moduleName = substr($moduleName, 0, strpos($moduleName, 'ModuleCenterModule'));
        
        $this->name = $moduleName;
    }
    
    
    /**
     * Set isInstalled flag
     */
    protected function _setIsInstalled()
    {
        $configurationKey  = self::LEGACY_PREFIX . 'MODULE_CENTER_' . strtoupper($this->getName()) . '_INSTALLED';
        $isInstalledResult = $this->db->select('value')
            ->from('gx_configurations')
            ->where('key', $configurationKey)
            ->get();
        $isInstalled       = $isInstalledResult->row();
        $this->isInstalled = $isInstalled ? (boolean)$isInstalled->value : null;
    }
    
    
    /**
     * Installs the module
     */
    public function install()
    {
        $this->isInstalled = true;
        $this->_store(true);
    }
    
    
    /**
     * Uninstalls the module
     */
    public function uninstall()
    {
        $this->isInstalled = false;
        $this->_store(false);
    }
    
    
    /**
     * Returns true, if the module is installed. Otherwise false is returned.
     *
     * @return bool
     */
    final public function isInstalled()
    {
        if (is_null($this->isInstalled)) {
            $this->_setIsInstalled();
        }
        
        return $this->isInstalled;
    }
    
    
    /**
     * Returns true, if the module should be displayed in module center.
     *
     * @return bool
     */
    public function isVisible()
    {
        return true;
    }
    
    
    /**
     * Returns the name of the module
     *
     * The name is filtered by a regular expression.
     * Only alphanumeric characters are allowed.
     *
     * @return string
     */
    final public function getName()
    {
        if (empty($this->name)) {
            $this->_setModuleName();
        }
        
        return preg_replace('[\W]', '', $this->name);
    }
    
    
    /**
     * Returns the title of the module
     *
     * @return string
     */
    final public function getTitle()
    {
        return substr(strip_tags($this->title), 0, 50);
    }
    
    
    /**
     * Returns the description of the module
     *
     * @return string
     */
    final public function getDescription()
    {
        return substr(strip_tags($this->description, '<br><i><strong><u>'), 0, 500);
    }
    
    
    /**
     * Returns the sort order of the module
     *
     * @return double
     */
    final public function getSortOrder()
    {
        return (double)$this->sortOrder;
    }
    
    
    final protected function _store($installed)
    {
        $installed        = (boolean)$installed;
        $configurationKey = self::LEGACY_PREFIX . 'MODULE_CENTER_' . strtoupper($this->getName()) . '_INSTALLED';
        
        $container = LegacyDependencyContainer::getInstance();
        
        /** @var ModulesConfigurationRepository $repository */
        $repository = $container->get(ModulesConfigurationRepository::class);
        $repository->updateConfiguration($configurationKey, (string)(int)$installed, self::LEGACY_PREFIX);
        
        $this->_clearCache();
    }
    
    
    /**
     * Empty modules cache after module installation
     */
    final protected function _clearCache()
    {
        $application = new Application(LeagueContainer::create());
        $adminBootstrapper = new GambioAdminBootstrapper();
        $adminBootstrapper->boot($application);
        
        /** @var AdminMenuService $menuService */
        $menuService = $application->get(AdminMenuService::class);
        $menuService->deleteMenuCache();
        
        $this->cacheControl->clear_data_cache();
    }
}