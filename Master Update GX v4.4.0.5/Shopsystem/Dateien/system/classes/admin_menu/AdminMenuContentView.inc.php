<?php
/* --------------------------------------------------------------
  AdminMenuContentView.inc.php 2015-10-07
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2015 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------
 */

use Gambio\Admin\Layout\Menu\AdminMenuService;

class AdminMenuContentView extends ContentView
{
    protected $customerId = 0;
    
    
    public function __construct()
    {
        parent::__construct();
        $this->set_template_dir(DIR_FS_CATALOG . 'admin/html/content/');
        $this->set_content_template('admin_menu.html');
        
        $this->init_smarty();
        $this->set_flat_assigns(false);
    }
    
    
    /**
     * Prepares the data for the gambio admin menu.
     *
     * The AdminMenuService is used to fetch the menu data from one source.
     * The dataset will be modified in order to accomplish the requirements for the legacy pages.
     *
     * @return array|void
     */
    public function prepare_data()
    {
        /** @var AdminMenuService $menuService */
        $legacyContainer = LegacyDependencyContainer::getInstance();
        $menuService     = $legacyContainer->get(AdminMenuService::class);
        
        $menu = $menuService->getAdminMenu();
        foreach ($menu as &$menuGroup) {
            $menuGroup['active_class'] = $menuGroup['isActive'] ? 'current' : '';
            foreach ($menuGroup['menuitems'] as &$menuitem) {
                $menuLink          = parse_url($menuitem['link']);
                $menuLinkParameter = [];
                if (isset($menuLink['query'])) {
                    parse_str($menuLink['query'], $menuLinkParameter);
                }
                $menuLinkPath = $menuLink['path'];
                if (count($menuLinkParameter) > 0) {
                    $menuLinkPath .= '?' . http_build_query($menuLinkParameter);
                }
                
                $isActive = $menuitem['isActive']
                            || ($menuLinkPath === AdminMenuControl::get_connected_page());
                
                $menuitem['class'] = $isActive ? 'current' : '';
                if ($isActive) {
                    $menuGroup['active_class'] = 'current';
                }
            }
            unset($menuitem);
        }
        unset($menuGroup);
        $this->set_content_data('DATA', $menu);
        
        return $menu;
    }
    
    
    public function setCustomerId($customerId)
    {
        $this->customerId = (int)$customerId;
    }
}