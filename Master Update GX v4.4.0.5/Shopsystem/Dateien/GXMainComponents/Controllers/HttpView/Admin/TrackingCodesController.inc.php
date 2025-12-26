<?php
/* --------------------------------------------------------------
   TrackingCodesController.inc.php 2019-12-04
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2019 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

class TrackingCodesController extends AdminHttpViewController
{
    /** @var LanguageTextManager */
    protected $text;
    
    
    public function proceed(HttpContextInterface $httpContext)
    {
        $this->text = MainFactory::create('LanguageTextManager', 'gm_analytics', $_SESSION['languages_id']);
        parent::proceed($httpContext);
    }
    
    
    public function actionDefault()
    {
        $title             = new NonEmptyStringType($this->text->get_text('page_title'));
        $template          = new ExistingFile(new NonEmptyStringType(DIR_FS_ADMIN
                                                                     . '/html/content/tracking_codes.html'));
        $dataArray         = [
            'pageToken'                                    => $_SESSION['coo_page_token']->generate_token(),
            'action_save_configuration'                    => xtc_href_link('admin.php',
                                                                            'do=TrackingCodes/SaveConfiguration'),
            'GM_HEAD_TRACKING_CODE_USE'                    => (bool)gm_get_conf('GM_HEAD_TRACKING_CODE_USE'),
            'GM_HEAD_TRACKING_CODE_USE_SMARTY'             => (bool)gm_get_conf('GM_HEAD_TRACKING_CODE_USE_SMARTY'),
            'GM_HEAD_TRACKING_CODE'                        => (string)gm_get_conf('GM_HEAD_TRACKING_CODE'),
            'GM_ANALYTICS_CODE_USE'                        => (bool)gm_get_conf('GM_ANALYTICS_CODE_USE'),
            'GM_ANALYTICS_CODE_USE_SMARTY'                 => (bool)gm_get_conf('GM_ANALYTICS_CODE_USE_SMARTY'),
            'GM_ANALYTICS_CODE'                            => (string)gm_get_conf('GM_ANALYTICS_CODE'),
            'GM_TRACKING_CODE_CHECKOUT_SUCCESS_USE'        => (bool)gm_get_conf('GM_TRACKING_CODE_CHECKOUT_SUCCESS_USE'),
            'GM_TRACKING_CODE_CHECKOUT_SUCCESS_USE_SMARTY' => (bool)gm_get_conf('GM_TRACKING_CODE_CHECKOUT_SUCCESS_USE_SMARTY'),
            'GM_TRACKING_CODE_CHECKOUT_SUCCESS'            => (string)gm_get_conf('GM_TRACKING_CODE_CHECKOUT_SUCCESS'),
        ];
        $data              = MainFactory::create('KeyValueCollection', $dataArray);
        $assets            = MainFactory::create('AssetCollection', []);
        $contentNavigation = MainFactory::create('ContentNavigationCollection', []);
        $contentNavigation->add(new StringType($this->text->get_text('BOX_GM_META', 'admin_menu')),
                                new StringType('gm_meta.php'),
                                new BoolType(false));
        $contentNavigation->add(new StringType($this->text->get_text('BOX_ROBOTS', 'admin_menu')),
                                new StringType('robots_download.php'),
                                new BoolType(false));
        $contentNavigation->add(new StringType($this->text->get_text('BOX_GM_SITEMAP', 'admin_menu')),
                                new StringType('gm_sitemap.php'),
                                new BoolType(false));
        $contentNavigation->add(new StringType($this->text->get_text('PAGE_TITLE', 'static_seo_urls')),
                                new StringType('admin.php?do=StaticSeoUrl'),
                                new BoolType(false));
        $contentNavigation->add(new StringType($this->text->get_text('BOX_GM_ANALYTICS', 'admin_menu')),
                                new StringType('admin.php?do=TrackingCodes'),
                                new BoolType(true));
        
        return MainFactory::create('AdminLayoutHttpControllerResponse',
                                   $title,
                                   $template,
                                   $data,
                                   $assets,
                                   $contentNavigation);
    }
    
    
    public function actionSaveConfiguration()
    {
        $this->_validatePageToken();
        
        $configuration = [
            'GM_HEAD_TRACKING_CODE_USE'                    => filter_var($this->_getPostData('GM_HEAD_TRACKING_CODE_USE'),
                                                                         FILTER_VALIDATE_BOOLEAN),
            'GM_HEAD_TRACKING_CODE_USE_SMARTY'             => filter_var($this->_getPostData('GM_HEAD_TRACKING_CODE_USE_SMARTY'),
                                                                         FILTER_VALIDATE_BOOLEAN),
            'GM_HEAD_TRACKING_CODE'                        => $this->_getPostData('GM_HEAD_TRACKING_CODE'),
            'GM_ANALYTICS_CODE_USE'                        => filter_var($this->_getPostData('GM_ANALYTICS_CODE_USE'),
                                                                         FILTER_VALIDATE_BOOLEAN),
            'GM_ANALYTICS_CODE_USE_SMARTY'                 => filter_var($this->_getPostData('GM_ANALYTICS_CODE_USE_SMARTY'),
                                                                         FILTER_VALIDATE_BOOLEAN),
            'GM_ANALYTICS_CODE'                            => $this->_getPostData('GM_ANALYTICS_CODE'),
            'GM_TRACKING_CODE_CHECKOUT_SUCCESS_USE'        => filter_var($this->_getPostData('GM_TRACKING_CODE_CHECKOUT_SUCCESS_USE'),
                                                                         FILTER_VALIDATE_BOOLEAN),
            'GM_TRACKING_CODE_CHECKOUT_SUCCESS_USE_SMARTY' => filter_var($this->_getPostData('GM_TRACKING_CODE_CHECKOUT_SUCCESS_USE_SMARTY'),
                                                                         FILTER_VALIDATE_BOOLEAN),
            'GM_TRACKING_CODE_CHECKOUT_SUCCESS'            => $this->_getPostData('GM_TRACKING_CODE_CHECKOUT_SUCCESS'),
        ];
        
        foreach ($configuration as $key => $value) {
            gm_set_conf($key, $value);
        }
        
        $GLOBALS['messageStack']->add_session($this->text->get_text('configuration_saved'), 'info');
        
        return MainFactory::create('RedirectHttpControllerResponse', xtc_href_link('admin.php', 'do=TrackingCodes'));
    }
}
