<?php
/* --------------------------------------------------------------
   SharedShoppingCartConfigurationController.inc.php 2020-06-09 gm
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

MainFactory::load_class('HttpViewController');

/**
 * Class SharedShoppingCartConfigurationController
 *
 * @category System
 * @package  HttpViewControllers
 */
class SharedShoppingCartConfigurationController extends HttpViewController
{
    /**
     * @var LanguageTextManager $languageTextManager
     */
    protected $languageTextManager;
    
    /**
     * @var int $defaultLifePeriod
     */
    protected $defaultLifePeriod = 365;
    
    
    /**
     * Initializes the controller
     *
     * @param HttpContextInterface $httpContext
     *
     * @throws LogicException
     */
    public function proceed(HttpContextInterface $httpContext)
    {
        $this->contentView->set_template_dir(DIR_FS_ADMIN . 'html/content/module_center/');
        parent::proceed($httpContext); // proceed http context from parent class
    }
    
    
    public function actionDefault()
    {
        $this->languageTextManager = MainFactory::create('LanguageTextManager',
                                                         'shared_shopping_cart_configuration',
                                                         (int)$_SESSION['languages_id']);
        $lifePeriod                = $this->_getShoppingCartLifePeriod();
        
        $title    = $this->languageTextManager->get_text('shared_shopping_cart_configuration_title');
        $template = 'module_center/shared_shopping_cart_configuration.html';
        $data     = ['life_period' => $lifePeriod];
        
        return AdminLayoutHttpControllerResponse::createAsLegacyAdminPageResponse($title, $template, $data);
    }
    
    
    public function actionStore()
    {
        $this->_storeLifePeriod(new IntType($this->_getPostData('life_period')));
        
        return new RedirectHttpControllerResponse(xtc_href_link('admin.php', 'do=SharedShoppingCartConfiguration'));
    }
    
    
    protected function _getShoppingCartLifePeriod()
    {
        $lifePeriod = gm_get_conf('SHARED_SHOPPING_CART_LIFE_PERIOD');
        if ($lifePeriod === null) {
            $lifePeriod = $this->defaultLifePeriod;
            $this->_storeLifePeriod(new IntType((int)$lifePeriod));
        }
        $lifePeriod = (int)$lifePeriod;
        
        return $lifePeriod;
    }
    
    
    protected function _storeLifePeriod(IntType $lifePeriod)
    {
        gm_set_conf('SHARED_SHOPPING_CART_LIFE_PERIOD', $lifePeriod->asInt());
    }
}