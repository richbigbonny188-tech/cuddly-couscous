<?php
/**
 * ProductQuestionController.inc.php 2021-02-15
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2021 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

/**
 * Class ProductQuestionController
 *
 * @extends    HttpViewController
 * @category   System
 * @package    HttpViewControllers
 */
class ProductQuestionController extends HttpViewController
{
    /**
     * This ContentView was converted to the this->tellAFriendContentView functionality swince v3.1.
     *
     * @var TellAFriendContentView
     */
    protected $tellAFriendContentView;
    
    /**
     * @var EmailService
     */
    protected $emailService;
    
    /**
     * @var CI_DB_query_builder
     */
    protected $db;
    
    /**
     * @var LanguageTextManager
     */
    protected $languageTextManager;
    
    
    /**
     * Initialize Controller
     */
    public function init()
    {
        $this->tellAFriendContentView = MainFactory::create('TellAFriendContentView');
        $this->emailService           = StaticGXCoreLoader::getService('Email');
        $this->db                     = StaticGXCoreLoader::getDatabaseQueryBuilder();
    }
    
    
    /**
     * Display the modal form.
     *
     * @return JsonHttpControllerResponse
     */
    public function actionDefault()
    {
        $this->_setupContentView();
        
        $response = [
            'success' => true,
            'content' => $this->tellAFriendContentView->get_html()
        ];
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * Send the question email.
     *
     * @return JsonHttpControllerResponse
     * @throws Exception
     */
    public function actionSend()
    {
        // Prepare success modal dialog.
        $this->_setupContentView();
        $this->tellAFriendContentView->setPost($_POST);
        $this->tellAFriendContentView->setName($_POST['name']);
        $this->tellAFriendContentView->setEmail($this->getSenderEmail());
        $this->tellAFriendContentView->setMessage($this->getEmailMessage());
        $this->tellAFriendContentView->setPrivacyAccepted(isset($_POST['privacy_accepted']) ? 1 : 0);
        
        $contentHtml  = $this->tellAFriendContentView->get_html();
        $contentArray = $this->tellAFriendContentView->get_content_array();
        
        $response = [
            'success' => !isset($contentArray['ERROR']),
            'content' => $contentHtml
        ];
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * Prepare the TellAFriendContentView instance.
     */
    protected function _setupContentView()
    {
        $this->tellAFriendContentView->setProductQuestionTemplate();
        $this->tellAFriendContentView->set_flat_assigns(false);
        $this->tellAFriendContentView->setProductsId((int)$_GET['productId']);
        
        $captcha = MainFactory::create_object('Captcha');
        $this->tellAFriendContentView->setCaptchaObject($_SESSION['captcha_object'] = &$captcha);
        
        $this->tellAFriendContentView->setCustomerId((int)$_SESSION['customer_id']);
        $this->tellAFriendContentView->setCustomerFirstName($_SESSION['customer_first_name']);
        $this->tellAFriendContentView->setCustomerLastName($_SESSION['customer_last_name']);
        $this->tellAFriendContentView->setLanguagesId((int)$_SESSION['languages_id']);
        
        if (array_key_exists('modifiers', $_GET) && is_array($_GET['modifiers']['property'])) {
            $this->tellAFriendContentView->setPropertyValueIds($_GET['modifiers']['property']);
        }
        
        if (array_key_exists('modifiers', $_GET) && is_array($_GET['modifiers']['attribute'])) {
            $this->tellAFriendContentView->setAttributeIds($_GET['modifiers']['attribute']);
        }

        if (array_key_exists('properties_values_ids', $_GET) && is_array($_GET['properties_values_ids'])) {
            $this->tellAFriendContentView->setPropertyValueIds($_GET['properties_values_ids']);
        }

        if (array_key_exists('id', $_GET) && is_array($_GET['id'])) {
            $this->tellAFriendContentView->setAttributeIds($_GET['id']);
        }
    }
    
    
    /**
     * @return bool
     * @throws Exception
     */
    protected function sendEmailFromCustomersEmail(): bool
    {
        return gm_get_conf('GM_TELL_A_FRIEND_USE_CUSTOMER_EMAIL') === 'true'
               && defined('EMAIL_FROM')
               && EMAIL_FROM !== '';
    }
    
    
    /**
     * @return string
     * @throws Exception
     */
    protected function getSenderEmail(): string
    {
        if ($this->sendEmailFromCustomersEmail()) {
            
            return $_POST['email'];
        }
        
        return EMAIL_FROM;
    }
    
    
    /**
     * @return string
     * @throws Exception
     */
    protected function getEmailMessage(): string
    {
        if ($this->sendEmailFromCustomersEmail()) {
            
            return $_POST['message'];
        }
        
        $message = $this->languageTextManager()->get_text('SHOW_TELL_A_FRIEND_USE_CUSTOMER_EMAIL_SENDER');
        $message .= $_POST['email'] . PHP_EOL;
        
        return $message . $_POST['message'];
    }
    
    
    /**
     * @return LanguageTextManager
     */
    protected function languageTextManager(): LanguageTextManager
    {
        if ($this->languageTextManager === null) {
    
            $this->languageTextManager = new LanguageTextManager('template_configuration');
        }
        
        return $this->languageTextManager;
    }
}