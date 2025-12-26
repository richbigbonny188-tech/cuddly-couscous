<?php
/* --------------------------------------------------------------
   EmbeddedModuleController.inc.php 2020-06-09
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

MainFactory::load_class('AdminHttpViewController');

/**
 * Class EmbeddedModuleController
 *
 * @category System
 * @package  AdminHttpViewControllers
 */
class EmbeddedModuleController extends AdminHttpViewController
{
    /**
     * Initializes the controller
     *
     * @param HttpContextInterface $httpContext
     */
    public function proceed(HttpContextInterface $httpContext)
    {
        $this->contentView->set_template_dir(DIR_FS_ADMIN . 'html/content/');
        parent::proceed($httpContext); // proceed http context from parent class
    }
    
    
    /**
     * Returns the embedded module page
     *
     * @param string $title
     * @param string $modulePath
     *
     * @return AdminLayoutHttpControllerResponse
     */
    public function actionDefault($title = '', $modulePath = '')
    {
        return AdminLayoutHttpControllerResponse::createAsLegacyAdminPageResponse($title,
                                                                                  'embedded_module.html',
                                                                                  ['module' => $modulePath]);
    }
    
    
    /**
     * Returns embedded phpminiadmin page
     *
     * @return AdminLayoutHttpControllerResponse
     */
    public function actionMinisql()
    {
        $languageTextManager = MainFactory::create_object('LanguageTextManager', [], true);
        $_SESSION['XSS']     = create_coupon_code('secret', 16);
        $pageToken           = $_SESSION['coo_page_token']->generate_token();
        
        return $this->actionDefault($languageTextManager->get_text('BOX_GM_SQL', 'admin_menu'),
                                    DIR_WS_ADMIN . 'phpminiadmin.php?XSS=' . $_SESSION['XSS'] . '&page_token='
                                    . $pageToken);
    }
}