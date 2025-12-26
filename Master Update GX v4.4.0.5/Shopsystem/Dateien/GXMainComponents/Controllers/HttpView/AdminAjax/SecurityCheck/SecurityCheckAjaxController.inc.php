<?php
/* --------------------------------------------------------------
 SecurityCheckAjaxController.php 2020-12-22
 Gambio GmbH
 http://www.gambio.de

 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

/**
 * Class SecurityCheckAjaxController
 */
class SecurityCheckAjaxController extends AdminHttpViewController
{
    /**
     * Returns the message stack from the old system for the new system
     * @return JsonHttpControllerResponse|mixed
     * @throws AuthenticationException
     * @todo get rid of it
     */
    public function actionMessages()
    {
        if (!$this->isAdmin()) {
            throw new AuthenticationException('No admin privileges. Please contact the administrator.');
        }
        
        $languageTextManager = MainFactory::create_object('LanguageTextManager', [], true);
        $textPhrases         = $languageTextManager->get_section_array('start');
        
        /** @var messageStack_ORIGIN $messageStack */
        $messageStack = $GLOBALS['messageStack'];
    
        include(DIR_WS_MODULES . FILENAME_SECURITY_CHECK);
    
        $this->installerDeletionMessage($messageStack, $languageTextManager);
        
        return MainFactory::create(JsonHttpControllerResponse::class,
                                   $this->parseConstants($messageStack->jsonSerialize(), $textPhrases));
    }
    
    
    /**
     * Manually exchange Constants with Text Phrases.
     *
     * @param array $messageStack
     * @param array $textPhrases
     *
     * @return array
     */
    protected function parseConstants(array $messageStack, array $textPhrases): array
    {
        foreach ($messageStack['messages'] as &$messageTypes) {
            foreach ($messageTypes as &$message) {
                foreach ($textPhrases as $key => $textPhrase) {
                    if (strpos($message['message'], $key) !== false) {
                        $message['message'] = str_replace($key, $textPhrase, $message['message']);
                    }
                }
    
                $message = $this->removeLinks($message);
            }
        }
        
        return $messageStack;
    }
    
    
    /**
     * Remove links and add it to the message as data.
     *
     * @param array $message
     *
     * @return array
     */
    protected function removeLinks(array &$message): array
    {
        if (strpos($message['message'], '<a ')) {
            $startPosition = strpos($message['message'], '<a ');
            $length        = strpos($message['message'], '</a>') - $startPosition + strlen('</a>');
            $htmlTag       = substr($message['message'], $startPosition, $length);

            $dom = new DOMDocument();

            $internalErrors = libxml_use_internal_errors(true);
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $htmlTag);
            libxml_use_internal_errors($internalErrors);

            $link = $dom->getElementsByTagName('a');

            foreach ($link as $node) {
                $message['link']['url']  = $node->getAttribute('href');
                $message['link']['text'] = ucfirst(strtolower($node->nodeValue));
                $message['message']      = trim(str_replace($htmlTag, " ", $message['message']));
            }
        }
        
        return $message;
    }
    
    
    /**
     * Check if the customer is the admin.
     *
     * @return bool Is the customer the admin?
     */
    protected function isAdmin(): ?bool
    {
        try {
            $this->validateCurrentAdminStatus();
            
            return true;
        } catch (LogicException $exception) {
            return false;
        }
    }
    
    
    /**
     * @param messageStack_ORIGIN $messageStack
     * @param LanguageTextManager $languageTextManager
     */
    protected function installerDeletionMessage(messageStack_ORIGIN $messageStack, LanguageTextManager $languageTextManager): void
    {
        require_once(DIR_FS_CATALOG . 'gm/inc/gm_xtc_href_link.inc.php');

        $devEnvironment = file_exists(DIR_FS_CATALOG . '.dev-environment');
        
        $tokenFiles = glob(DIR_FS_CATALOG . 'media/secure_token_*');
    
        if(!is_array($tokenFiles) || count($tokenFiles) !== 1)
        {
            $tokenMessage = $languageTextManager->get_text('WARNING_TOKEN_COUNT', 'general');
            $GLOBALS['messageStack']->add($tokenMessage, 'error');
        }else{
            foreach($tokenFiles as $tokenFile)
            {
                $securityToken = (string)str_replace('secure_token_', '', basename($tokenFile));
            }
        }
        
        $installerExists = file_exists(DIR_FS_CATALOG . 'gambio_installer/request_port.php');
    
        if(isset($_GET['installerdeletionstatus']))
        {
            if($_GET['installerdeletionstatus'] == 1)
            {
                $messageStack->add($languageTextManager->get_text('INSTALL_DIRECTORY_DELETE_SUCCESS', 'general'), 'info');
            }
            else
            {
                $messageStack->add($languageTextManager->get_text('INSTALL_DIRECTORY_DELETE_FAIL', 'general'), 'error');
            }
        }
        elseif($installerExists && $devEnvironment === false && $_SESSION['customers_status']['customers_status_id'] === '0')
        {
            if(!empty($securityToken))
            {
                $installerMessage = sprintf($languageTextManager->get_text('WARNING_INSTALL_DIRECTORY_EXISTS_ACTION', 'general'),
                                            substr(DIR_WS_CATALOG, 0, -1),
                                            gm_xtc_href_link('gambio_installer/index.php', 'delete_installer&auth_token='.
                                                                                           $securityToken . '&return_url=' . gm_xtc_href_link('admin/')));
            }
            else
            {
                $installerMessage = sprintf($languageTextManager->get_text('WARNING_INSTALL_DIRECTORY_EXISTS', 'general'),
                                            substr(DIR_WS_CATALOG, 0, -1));
            }
        
            $messageStack->add($installerMessage, 'error');
        }
    }
}