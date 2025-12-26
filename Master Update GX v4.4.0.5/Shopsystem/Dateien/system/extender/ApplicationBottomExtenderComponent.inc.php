<?php
/* --------------------------------------------------------------
  ApplicationBottomExtenderComponent.inc.php 2021-02-01
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2021 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------
 */

MainFactory::load_class('ExtenderComponent');

/**
 * Class ApplicationBottomExtenderComponent
 */
class ApplicationBottomExtenderComponent extends ExtenderComponent
{
    public $v_page = false;

    /**
     * @var string|null
     */
    protected $shopPath;
    

    public function init_page(): void
    {
        $t_script_name = $this->get_script_name();

        $t_page = '';
        if (isset($this->v_data_array['GET']['coID']) && $this->v_data_array['GET']['coID'] == 14) {
            $t_page = PageType::CALLBACK_SERVICE;
        } elseif (!empty($this->v_data_array['GET']['manufacturers_id'])
                  && substr_count($t_script_name,
                                  'index.php') > 0) {
            $t_page = PageType::MANUFACTURERS;
        } elseif (substr_count($t_script_name, 'product_info.php') > 0) {
            $t_page = PageType::PRODUCT_INFO;
        } elseif (!empty($this->v_data_array['GET']['cat'])
                  || substr_count($_SERVER["QUERY_STRING"],
                                  'cat=') > 0
                  || substr_count($_SERVER["REQUEST_URI"],
                                  'cat/') > 0
                  || isset($this->v_data_array['GET']['filter_fv_id'])
                  || isset($this->v_data_array['GET']['filter_price_min'])
                  || isset($this->v_data_array['GET']['filter_price_max'])
                  || isset($this->v_data_array['GET']['filter_id'])) {
            $t_page = PageType::CAT;
        } elseif (substr_count($t_script_name, 'advanced_search_result.php') > 0) {
            $t_page = PageType::SEARCH;
        } elseif (substr_count($t_script_name, 'logoff.php') > 0) {
            $t_page = PageType::LOGOFF;
        } elseif (substr_count($t_script_name, 'gm_price_offer.php') > 0) {
            $t_page = PageType::PRICE_OFFER;
        } elseif (substr_count($t_script_name, 'shopping_cart.php') > 0) {
            $t_page = PageType::CART;
        } elseif (substr_count($t_script_name, 'shop_content.php') > 0) {
            $t_page = PageType::CONTENT;
        } elseif (substr_count($t_script_name, 'wish_list.php') > 0) {
            $t_page = PageType::WISH_LIST;
        } elseif (substr_count($t_script_name, 'address_book_process.php') > 0) {
            $t_page = PageType::ADDRESS_BOOK_PROCESS;
        } elseif (substr_count($t_script_name, 'gv_send.php') > 0) {
            $t_page = PageType::GV_SEND;
        } elseif (substr_count($t_script_name, 'checkout_') > 0) {
            $t_page = PageType::CHECKOUT;
        } elseif (preg_match('#account_history(_info)?\.php#', $t_script_name) === 1) {
            $t_page = PageType::ACCOUNT_HISTORY;
        } elseif ((substr_count($t_script_name, 'account.php') > 0)
                  || (substr_count($t_script_name, 'address_book.php') > 0)
                  || (substr_count($t_script_name, 'account_password.php') > 0)
                  || (substr_count($t_script_name, 'gm_account_delete.php') > 0)
                  || (substr_count($t_script_name, 'shop.php') > 0
                      && (substr_count($_GET['do'], 'CreateRegistree') > 0
                          || substr_count($_GET['do'], 'CreateGuest') > 0))) {
            $t_page = PageType::ACCOUNT;
        } elseif (substr_count(strtolower($t_script_name), 'index.php') > 0) {
            $t_page = PageType::INDEX;
        } elseif (substr_count($t_script_name, 'withdrawal.php') > 0) {
            $t_page = PageType::WITHDRAWAL;
        }

        $this->v_page = $t_page;
        
        
    }
    
    
    /**
     *
     */
    protected function create_page_identification_tag(): void
    {
        $id = "";
        if($this->get_page()){
            $id = $this->convert_case($this->get_page());
        }

        //additional validation for style edit
        if($this->get_page() === PageType::PRODUCT_INFO && isset($_COOKIE['STYLE_EDIT_PREVIEW_THEME'])) {
            $id = $this->update_product_info_page_id();
        }

        $this->v_output_buffer['PAGE_ID_TAG'] .= "<input type='hidden' id='page_namespace' value='{$id}'/>";
    }
    
    
    /**
     * @param $input
     *
     * @return string
     */
    protected function convert_case($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return strtoupper(implode('_', $ret));
        
    }


    /**
     * @return string
     */
    protected function get_script_name(): string
    {
        if (strpos($_SERVER['SCRIPT_NAME'], '.php') !== false) {
            if (strpos($_SERVER['SCRIPT_NAME'], DIR_WS_CATALOG) !== false) {
                return $_SERVER['SCRIPT_NAME'];
            } elseif (strpos($_SERVER['PHP_SELF'], DIR_WS_CATALOG) !== false) {
                return $_SERVER["PHP_SELF"];
            } elseif (strpos($_SERVER['SCRIPT_FILENAME'], DIR_WS_CATALOG) !== false) {
                return $_SERVER['SCRIPT_FILENAME'];
            }
        }
        return $PHP_SELF;

}


    function init_js()
    {
        $this->v_output_buffer = [];
        $this->create_page_identification_tag();
        if ((gm_get_conf('GM_SHOP_OFFLINE') != 'checked'
             || $_SESSION['customers_status']['customers_status_id'] == 0)) {
            $t_get_data_array = [];
            $t_page           = $this->get_page();

            if ($t_page == PageType::PRODUCT_INFO) {
                $t_get_data_array[] = 'cPath=' . $this->v_data_array['cPath'];
                $t_get_data_array[] = 'products_id=' . $this->v_data_array['products_id'];
            } elseif ($t_page == PageType::CAT) {
                $t_get_data_array[] = 'cPath=' . $this->v_data_array['cPath'];
            }

            if (!empty($t_page)) {
                $t_get_data_array[] = 'page=' . $t_page;
            }

            // open the MiniWK with this param
            if (isset($_GET['open_cart_dropdown']) && $_GET['open_cart_dropdown'] == 1) {
                $t_get_data_array[] = 'open_cart_dropdown=1';
            }

            $t_get_data_array[] = 'current_template=' . StaticGXCoreLoader::getThemeControl()->getCurrentTheme();

            $t_get_data_array[] = 'no_boost';

            $cacheToken  = MainFactory::create('CacheTokenHelper')->getCacheToken();
            $isDebugMode = file_exists($this->getShopPath() . '.dev-environment');

            $suffix        = '.min';
            $bustParameter = '?bust=' . $cacheToken;
            $bustSuffix    = '';
            if ($isDebugMode) {
                $suffix        = '';
                $bustParameter = '';
            }
            if ($isDebugMode === false
                && isset($_SERVER['gambio_mod_rewrite_working'], $_SERVER['gambio_htaccessVersion'])
                && (bool)$_SERVER['gambio_mod_rewrite_working']
                && version_compare($_SERVER['gambio_htaccessVersion'], '2.8') >= 0
                && @constant('USE_BUSTFILES') === 'true') {
                $bustSuffix    = '-bust_' . $cacheToken;
                $bustParameter = '';
            }

            $dataCache   = DataCache::get_instance();
            $renewInitJs = false;
            if ($dataCache->get_persistent_data('init_js-' . $_SESSION['language_code']) !== $cacheToken) {
                $renewInitJs = true;
            }

            $initJsFilename = $this->getShopPath() . StaticGXCoreLoader::getThemeControl()->getThemeJsPath() . 'init-'
                              . $_SESSION['language_code'] . $suffix . '.js';

            if ($renewInitJs || !file_exists($initJsFilename)) {
                // page token will be set in separate script block to avoid caching problems
                $pageToken      = '';
                $jsEngineConfig = MainFactory::create('JSEngineConfiguration',
                                                      new NonEmptyStringType(GM_HTTP_SERVER . DIR_WS_CATALOG),
                                                      new NonEmptyStringType(StaticGXCoreLoader::getThemeControl()
                                                                                 ->getThemePath()),
                                                      new LanguageCode(new StringType($_SESSION['language_code'])),
                                                      MainFactory::create_object('LanguageTextManager',
                                                                                 [],
                                                                                 true),
                                                      new EditableKeyValueCollection([
                                                                                         'buttons'  => 'buttons',
                                                                                         'general'  => 'general',
                                                                                         'labels'   => 'labels',
                                                                                         'messages' => 'messages'
                                                                                     ]),
                                                      new BoolType($isDebugMode),
                                                      new StringType($pageToken),
                                                      new StringType($cacheToken));

                $viewType = StaticGXCoreLoader::getThemeControl()->isThemeSystemActive() ? 'theme' : 'template';

                $initJsContent = $jsEngineConfig->getJavaScript() . "\n";
                $initJsContent .= $this->load_file('JSEngine/build/vendor' . $suffix . '.js');
                $initJsContent .= $this->load_file_from_js_path('vendor' . $suffix . '.js');
                $initJsContent .= $this->load_file('JSEngine/build/jse' . $suffix . '.js');
                $initJsContent .= $this->load_file_from_js_path("initialize_$viewType" . $suffix . '.js');
                $initJsContent .= $this->load_file_from_js_path("{$viewType}_helpers" . $suffix . '.js');

                # Add global JS from GXModule directory
                $gxModuleFiles = GXModulesCache::getInstalledModuleFiles();
                $directoryName = StaticGXCoreLoader::getThemeControl()
                    ->isThemeSystemActive() ? 'Themes' : 'Templates';
                $addedJsFiles  = [];
                foreach (array_reverse(StaticGXCoreLoader::getThemeControl()
                                           ->getCurrentThemeHierarchy()) as $currentTheme) {
                    foreach ($gxModuleFiles as $file) {
                        if (substr($file, -3) === '.js' && !in_array($file, $addedJsFiles)
                            && (stripos($file,
                                        '/' . $directoryName . '/' . $currentTheme . '/Javascript/Global/')
                                !== false
                                || stripos($file, '/' . $directoryName . '/All/Javascript/Global/') !== false)) {
                            $initJsContent  .= file_get_contents($file) . "\n";
                            $addedJsFiles[] = $file;
                        }
                    }
                }

                if (file_exists($initJsFilename)) {
                    unlink($initJsFilename);
                }

                file_put_contents($initJsFilename, $initJsContent);

                $dataCache->write_persistent_data('init_js-' . $_SESSION['language_code'], $cacheToken);
            }

            if ($isDebugMode) {
                // page token will be set in separate script block to avoid caching problems
                $pageToken      = '';
                $jsEngineConfig = MainFactory::create('JSEngineConfiguration',
                                                      new NonEmptyStringType(GM_HTTP_SERVER . DIR_WS_CATALOG),
                                                      new NonEmptyStringType(StaticGXCoreLoader::getThemeControl()
                                                                                 ->getThemePath()),
                                                      new LanguageCode(new StringType($_SESSION['language_code'])),
                                                      MainFactory::create_object('LanguageTextManager',
                                                                                 [],
                                                                                 true),
                                                      new EditableKeyValueCollection([
                                                                                         'buttons'  => 'buttons',
                                                                                         'general'  => 'general',
                                                                                         'labels'   => 'labels',
                                                                                         'messages' => 'messages'
                                                                                     ]),
                                                      new BoolType($isDebugMode),
                                                      new StringType($pageToken),
                                                      new StringType($cacheToken));

                $this->v_output_buffer['GM_JAVASCRIPT_CODE'] = '<script>' . $jsEngineConfig->getJavaScript()
                                                               . '</script>' . "\n\t\t";

                $this->v_output_buffer['GM_JAVASCRIPT_CODE'] .= '<script src="JSEngine/build/vendor' . $suffix
                                                                . '.js"></script>' . "\n\t\t";
                $this->v_output_buffer['GM_JAVASCRIPT_CODE'] .= '<script src="'
                                                                . StaticGXCoreLoader::getThemeControl()
                                                                    ->getThemeJsPath() . 'vendor' . $suffix
                                                                . '.js"></script>' . "\n\t\t";
                $this->v_output_buffer['GM_JAVASCRIPT_CODE'] .= '<script src="JSEngine/build/jse' . $suffix
                                                                . '.js"></script>' . "\n\t\t";
                if (!StaticGXCoreLoader::getThemeControl()->isThemeSystemActive()) {
                    $this->v_output_buffer['GM_JAVASCRIPT_CODE'] .= '<script src="'
                                                                    . StaticGXCoreLoader::getThemeControl()
                                                                        ->getThemeJsPath() . 'initialize_template'
                                                                    . $suffix . '.js"></script>' . "\n\t\t";
                    $this->v_output_buffer['GM_JAVASCRIPT_CODE'] .= '<script src="'
                                                                    . StaticGXCoreLoader::getThemeControl()
                                                                        ->getThemeJsPath() . 'template_helpers'
                                                                    . $suffix . '.js"></script>' . "\n\t\t";
                } else {
                    $this->v_output_buffer['GM_JAVASCRIPT_CODE'] .= '<script src="'
                                                                    . StaticGXCoreLoader::getThemeControl()
                                                                        ->getThemeJsPath() . 'initialize_theme'
                                                                    . $suffix . '.js"></script>' . "\n\t\t";
                    $this->v_output_buffer['GM_JAVASCRIPT_CODE'] .= '<script src="'
                                                                    . StaticGXCoreLoader::getThemeControl()
                                                                        ->getThemeJsPath() . 'theme_helpers'
                                                                    . $suffix . '.js"></script>' . "\n\t\t";
                }

                # Add global JS from GXModule directory
                $gxModuleFiles = GXModulesCache::getInstalledModuleFiles();
                $directoryName = StaticGXCoreLoader::getThemeControl()
                    ->isThemeSystemActive() ? 'Themes' : 'Templates';
                $addedJsFiles  = [];
                foreach (array_reverse(StaticGXCoreLoader::getThemeControl()
                                           ->getCurrentThemeHierarchy()) as $currentTheme) {
                    foreach ($gxModuleFiles as $file) {
                        if (substr($file, -3) === '.js' && !in_array($file, $addedJsFiles)
                            && (stripos($file,
                                        '/' . $directoryName . '/' . $currentTheme . '/Javascript/Global/')
                                !== false
                                || stripos($file, '/' . $directoryName . '/All/Javascript/Global/') !== false)) {
                            $file                                        = substr($file, strlen($this->getShopPath()));
                            $this->v_output_buffer['GM_JAVASCRIPT_CODE'] .= '<script src="' . $file . '"></script>'
                                                                            . "\n\t\t";
                            $addedJsFiles[]                              = $file;
                        }
                    }
                }
            } else {
                $this->v_output_buffer['GM_JAVASCRIPT_CODE'] = '<script src="'
                                                               . StaticGXCoreLoader::getThemeControl()
                                                                   ->getThemeJsPath() . 'init-'
                                                               . $_SESSION['language_code'] . $bustSuffix . $suffix
                                                               . '.js' . $bustParameter . '" data-page-token="'
                                                               . $_SESSION['coo_page_token']->generate_token()
                                                               . '" id="init-js"></script>' . "\n\t\t";
            }

            if (StaticGXCoreLoader::getThemeControl()->isThemeSystemActive()) {
                
                $this->init_custom_theme_js($t_page ?? null);
            }
            $usermodJsMaster = MainFactory::create('UsermodJSMaster', $t_page);
            if (count($usermodJsMaster->get_files())) {
                $this->v_output_buffer['GM_JAVASCRIPT_CODE'] .= '<script src="gm_javascript.js.php?'
                                                                . implode('&amp;', $t_get_data_array)
                                                                . '"></script>' . "\n\t\t";
            }
        }

    }


    function get_page()
    {
        return $this->v_page;
    }


    /**
     * @param $filename
     *
     * @return string
     */
    protected function load_file($filename)
    {
        $result   = '';
        $filename = $this->getShopPath() . $filename;
        if (file_exists($filename)) {
            $result = file_get_contents($filename) . "\n";
        }

        return $result;
    }


    /**
     * @param $filename
     *
     * @return string
     */
    protected function load_file_from_js_path($filename)
    {
        $filename = StaticGXCoreLoader::getThemeControl()->getThemeJsPath() . $filename;

        return $this->load_file($filename);
    }


    /**
     * Includes theme custom scripts to the bottom
     *
     * @param string|null $page
     */
    protected function init_custom_theme_js($page)
    {
        // Set correct path of the theme
        $publishedThemePath = StyleEditServiceFactory::service()->getPublishedThemePath();
        $arguments = $publishedThemePath ?
            [ "{$publishedThemePath}/javascripts/system" ] :
            null;
        $customThemeJavaScriptCacheControl = MainFactory::create_object(
            'CustomThemeJavaScriptController',
            $arguments
        );
    
        $sectionScripts = $customThemeJavaScriptCacheControl->getJavaScripts($page);
        $isDebugMode    = file_exists($this->getShopPath() . '.dev-environment');
        
        if ($isDebugMode === false && count($sectionScripts) > 1) {
    
            $cacheBuilder   = new ThemeSectionJavascriptCacheBuilder;
            $sectionScripts = [$cacheBuilder->createCacheFile($page, $sectionScripts)];
        }
        
        $this->append_custom_js($sectionScripts);

        $variantDirectoryPath = $this->getShopPath() . StaticGXCoreLoader::getThemeControl()->getPublishedThemePath() . '/variants';

        if (is_dir($variantDirectoryPath)) {
            $variantDirectory = new ExistingDirectory($variantDirectoryPath);

            $themeJavaScriptOverloadControl = MainFactory::create_object('ThemeJavaScriptOverloadController',
                                                                         [$variantDirectory]);

            $this->v_output_buffer['GM_JAVASCRIPT_CODE'] = $themeJavaScriptOverloadControl->overloadJavaScripts($this->v_output_buffer['GM_JAVASCRIPT_CODE']);
        }
    }


    /**
     * appends a list of custom javascript's
     *
     * @param string[] $javaScriptPaths
     */
    protected function append_custom_js($javaScriptPaths)
    {
        if (count($javaScriptPaths) > 0) {
    
            $cacheFriendlyFileNames = defined('USE_BUSTFILES') && USE_BUSTFILES === 'true';
            $cacheToken             = MainFactory::create('CacheTokenHelper')->getCacheToken();
            
            foreach ($javaScriptPaths as $js) {
    
                $relativePath = str_replace($this->getShopPath(), '', $js);
                $fileName     = basename($relativePath);
                $pathToScript = preg_replace('#/[^/]+$#', '/', $relativePath);
                
                if ($cacheFriendlyFileNames === true) {
    
                    $pattern                  = '#^(.*)(\.min)?(\.js)$#';
                    $fileNameWithOutExtension = preg_replace($pattern, '$1', $fileName);
                    $fileExtension            = preg_replace($pattern, '$2$3', $fileName);
                    
                    $fileName = $fileNameWithOutExtension . '-bust_' . $cacheToken . $fileExtension;
                } else {
                    $fileName .= '?bust=' . $cacheToken;
                }
    
                $this->v_output_buffer['GM_JAVASCRIPT_CODE'] .= '<script src="';
                $this->v_output_buffer['GM_JAVASCRIPT_CODE'] .= $pathToScript . $fileName;
                $this->v_output_buffer['GM_JAVASCRIPT_CODE'] .= '"></script>' . PHP_EOL;
            }
        }
    }


    function proceed()
    {
        $t_page = $this->get_page();
        if ($t_page === false) {
            trigger_error('need call of init_page() method before proceed', E_USER_ERROR);
        }

        parent::proceed();
    }


    protected function getShopPath(): string
    {
        if($this->shopPath === null) {
            $this->shopPath = realpath(DIR_FS_CATALOG);
            $this->shopPath = str_replace('\\', '/', $this->shopPath) . '/';
        }
        
        return $this->shopPath;
    }

    /**
     *
     */
    protected function update_product_info_page_id()
    {
        $product = $GLOBALS['product'] ?? null;
        if($product && $product instanceof product && $product->isProduct()){


            $sqlProductWithModifiers = "
            select min(products_id) products_id
            from (
                     select min(products.products_id) products_id
                     from products_properties_combis
                              inner join products on products.products_id = products_properties_combis.products_id
                     where products.products_status = 1
                     union
                     select min(products_attributes.products_id)
                     from products_attributes
                              inner join products on products.products_id = products_attributes.products_id
                     where products.products_status = 1
                 ) ids";
            $products = StaticGXCoreLoader::getDatabaseQueryBuilder()->query($sqlProductWithModifiers)->result_array();
            if (count($products)) {
                if((int)$products[0]['products_id'] === (int)$product->data['products_id']) {
                    return 'PRODUCT_INFO_WITH_MODIFIERS';
                }
            }
        }
        return 'PRODUCT_INFO';
    }
}
