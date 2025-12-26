<?php
/* --------------------------------------------------------------
   CacheControl.inc.php 2021-02-02
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

use Gambio\Admin\Application\GambioAdminBootstrapper;
use Gambio\Core\Application\DependencyInjection\LeagueContainer;
use Gambio\Core\Application\Application;
use Gambio\Core\Cache\CacheFactory;
use Gambio\Admin\Layout\Menu\AdminMenuService;

/**
 * Description of CacheControl
 *
 *
 * @author ncapuno
 */
class CacheControl
{
    public function __construct()
    {
        MainFactory::load_class(StyleEditServiceFactory::class);
    }
    
    
    public function reset_cache($p_cache_group = 'all')
    {
        switch ($p_cache_group) {
            case 'styles':
                $this->clear_google_font_cache();
                $this->clear_css_cache();
                break;
            
            case 'modules':
                $this->clear_data_cache();
                $this->clear_content_view_cache();
                $this->clear_templates_c();
                break;
            
            case 'configuration':
                $this->clear_data_cache();
                $this->clear_content_view_cache();
                $this->clear_google_font_cache();
                $this->clear_css_cache();
                break;
            
            case 'categories':
                $this->rebuild_products_categories_index();
                $this->clear_data_cache();
                $this->clear_content_view_cache();
                break;
            
            case 'products':
                $this->clear_content_view_cache();
                $this->rebuild_products_categories_index();
                break;
            
            case 'features':
                $this->rebuild_feature_index();
                break;
            
            case 'properties':
                $this->rebuild_products_properties_index();
                break;
            
            case 'all':
            default:
                $this->clear_data_cache();
                $this->clear_content_view_cache();
                $this->clear_templates_c();
                $this->clear_google_font_cache();
                $this->clear_css_cache();   
                $this->rebuild_products_categories_index();
                $this->rebuild_products_properties_index();
                $this->rebuild_feature_index();
        }
    }
    
    
    public function set_reset_token()
    {
        $t_token_file    = DIR_FS_CATALOG . 'cache/reset_cache';
        $t_token_content = 'reset_cache';
        
        $fp = fopen($t_token_file, 'w');
        fwrite($fp, $t_token_content);
        fclose($fp);
    }
    
    
    public function remove_reset_token()
    {
        if ($this->reset_token_exists()) {
            $t_token_file = DIR_FS_CATALOG . 'cache/reset_cache';
            unlink($t_token_file);
            
            $coo_admin_infobox_control = MainFactory::create_object('AdminInfoboxControl');
            $coo_admin_infobox_control->delete_by_identifier('clear_cache');
        }
    }
    
    
    public function reset_token_exists()
    {
        $t_output     = false;
        $t_token_file = DIR_FS_CATALOG . 'cache/reset_cache';
        
        if (file_exists($t_token_file)) {
            $t_output = true;
        }
        
        return $t_output;
    }
    
    
    /*
     * CategoriesAgent
     * LanguageTextManager
     * ClassRegistry
     * CachedDirectory
     * AdminMenu
     */
    public function clear_data_cache()
    {
        $coo_cache = DataCache::get_instance();
        $coo_cache->clear_cache();

        $this->clear_module_registry_cache();
        $this->clear_gx_modules_cache();
    }
    
    
    public function clear_menu_cache()
    {
        $application = new Application(LeagueContainer::create());
        $adminBootstrapper = new GambioAdminBootstrapper();
        $adminBootstrapper->boot($application);

        /** @var AdminMenuService $menuService */
        $menuService = $application->get(AdminMenuService::class);
        $menuService->deleteMenuCache();
    }
    
    
    public function clear_content_view_cache()
    {
        $coo_cache = DataCache::get_instance();
        $coo_cache->clear_cache_by_tag('TEMPLATE');
        
        $t_dir          = DIR_FS_CATALOG . 'cache/';
        $t_file_pattern = 'view_*.html*';
        
        # get list of content_view cache files
        $t_cache_files_array = glob($t_dir . $t_file_pattern);
        if (is_array($t_cache_files_array) == false) {
            return true;
        }
        
        foreach ($t_cache_files_array as $t_cache_file) {
            # delete found cache files
            unlink($t_cache_file);
        }
        
        return true;
    }
    
    
    public function clear_google_font_cache()
    {
        $directory = DIR_FS_CATALOG . 'public/fonts/';
        
        if (!file_exists($directory)) {
            @mkdir($directory, 0777);
            @chmod($directory, 0777);
        }
        
        foreach (scandir($directory) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            unlink($directory . $file);
        }
        
        return true;
    }
    
    
    public function clear_templates_c()
    {
        $t_dir          = DIR_FS_CATALOG . 'cache' . DIRECTORY_SEPARATOR . 'smarty' . DIRECTORY_SEPARATOR;
        $t_file_pattern = '*.php';
        
        # get list of content_view cache files
        $t_cache_files_array = glob($t_dir . $t_file_pattern);
        if (is_array($t_cache_files_array) == false) {
            return true;
        }
        
        foreach ($t_cache_files_array as $t_cache_file) {
            # delete found cache files
            unlink($t_cache_file);
        }
        
        return true;
    }
    
    
    public function clear_template_cache()
    {
        if (StyleEditServiceFactory::service()->styleEditIsInstalled()) {
            $styleEditCacheFiles = StyleEditServiceFactory::service()->getCacheFiles();
            
            if (is_array($styleEditCacheFiles)) {
                foreach ($styleEditCacheFiles as $seFile) {
                    unlink($seFile);
                }
            }
        }
        
        return true;
    }
    
    
    public function clear_css_cache()
    {
        $cssFiles = glob(DIR_FS_CATALOG . 'cache/__dynamics*.css');
        
        if (is_array($cssFiles)) {
            foreach ($cssFiles as $file) {
                unlink($file);
            }
        }
        
        if (defined('CURRENT_TEMPLATE')) {
            $cssPath = DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getThemePath();
            
            if (StaticGXCoreLoader::getThemeControl()->isThemeSystemActive()) {
                $cssPath .= 'styles/system/';
            }
            
            if (file_exists($cssPath . 'main.min.css')) {
                unlink($cssPath . 'main.min.css');
            }
            
            if (file_exists($cssPath . 'main.css')) {
                unlink($cssPath . 'main.css');
            }
        }
    }
    
    
    public function rebuild_feature_index()
    {
        $coo_feature_set_source = MainFactory::create_object('FeatureSetSource');
        $coo_feature_set_source->delete_empty_feature_sets();
        
        xtc_db_query('DELETE FROM feature_index');
        
        $t_result = xtc_db_query('SELECT * FROM feature_set');
        while (($t_row = mysqli_fetch_array($t_result))) {
            $coo_feature_set_source->build_feature_set_index($t_row['feature_set_id']);
        }
    }
    
    
    public function clear_orphaned_combi_values()
    {
        $t_sql = '
			DELETE FROM
				`products_properties_combis_values`
			WHERE
				`products_properties_combis_values_id` IN (
					SELECT
						`ppcv_id`
					FROM (
		                SELECT DISTINCT
		                    `ppcv`.`products_properties_combis_values_id` AS `ppcv_id`
		                FROM
		                    `products_properties_combis_values` AS `ppcv`
		                LEFT JOIN
		                    `products_properties_combis` AS `ppc`
				        ON
				            `ppcv`.`products_properties_combis_id` = `ppc`.`products_properties_combis_id`
		                WHERE
		                    `ppc`.`products_properties_combis_id` IS NULL
                    ) AS `source`
                )';
        xtc_db_query($t_sql);
    }
    
    
    public function rebuild_products_properties_index($p_products_id_array = null)
    {
        $this->clear_orphaned_combi_values();
        
        $coo_properties_data_agent = MainFactory::create_object('PropertiesDataAgent');
        
        if (isset($p_products_id_array)) {
            # rebuild given products_ids
            $t_where_or_part = implode(' OR products_id = ', $p_products_id_array);
            $t_where_or_part = ((isset($GLOBALS["___mysqli_ston"])
                                 && is_object(
                                     $GLOBALS["___mysqli_ston"]
                                 )) ? mysqli_real_escape_string(
                $GLOBALS["___mysqli_ston"],
                $t_where_or_part
            ) : ((trigger_error(
                "[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.",
                E_USER_ERROR
            )) ? "" : ""));
            
            $t_sql = 'DELETE FROM products_properties_index WHERE products_id = ' . $t_where_or_part;
            xtc_db_query($t_sql);
            
            $t_sql    = '
				SELECT products_id
				FROM products_properties_combis
				WHERE products_id = ' . $t_where_or_part . '
				GROUP BY products_id
			';
            $t_result = xtc_db_query($t_sql);
            
            while (($t_row = xtc_db_fetch_array($t_result))) {
                $coo_properties_data_agent->rebuild_properties_index($t_row['products_id']);
            }
        } else {
            # no products_ids given -> rebuild all products!
            $coo_properties_data_agent->rebuild_properties_index();
        }
    }
    
    
    public function rebuild_products_categories_index($p_products_id_array = null)
    {
        $coo_categories_index = MainFactory::create_object('CategoriesIndex');
        
        if (isset($p_products_id_array)) {
            # rebuild given products_ids
            for ($i = 0; $i < sizeof($p_products_id_array); $i++) {
                $coo_categories_index->build_categories_index($p_products_id_array[$i]);
            }
        } else {
            # no products_ids given -> rebuild all products!
            $coo_categories_index->build_categories_index();
        }
    }
    
    
    public function clear_expired_shared_shopping_carts()
    {
        $sharedShoppingCartService = StaticGXCoreLoader::getService('SharedShoppingCart');
        $sharedShoppingCartService->deleteExpiredShoppingCarts();
    }
    
    # DEPRECATED
    # backward compatibility wrappers
    public function clear_cache()
    {
        $this->reset_cache();
    }
    
    
    public function clear_cache_dir()
    {
        $this->clear_content_view_cache();
    }
    
    
    public function clear_compile_dir()
    {
        $this->clear_templates_c();
    }
    
    
    public function clear_shop_offline_page_cache()
    {
        $fileFlag = DIR_FS_CATALOG . 'cache/update_shop_offline_page_css.flag';
        
        if (!file_exists($fileFlag)) {
            touch($fileFlag);
        }
    }
    
    
    public function clear_text_cache()
    {
        /** @var CacheFactory $cacheFactory */
        $cacheFactory = LegacyDependencyContainer::getInstance()->get(CacheFactory::class);
        $cacheFactory->createCacheFor('text_cache')->clear();
    }
    
    
    public function clear_module_registry_cache()
    {
        $this->delete_files_by_pattern(DIR_FS_CATALOG . 'cache/module_registry-*.cache');
    }


    public function clear_gx_modules_cache()
    {
        $this->delete_files_by_pattern(DIR_FS_CATALOG . 'cache/gx_modules-*.cache');
    }
    
    
    /**
     * @param string $p_pattern
     */
    protected function delete_files_by_pattern($p_pattern)
    {
        $files = glob($p_pattern);
        
        if (is_array($files)) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }
}
