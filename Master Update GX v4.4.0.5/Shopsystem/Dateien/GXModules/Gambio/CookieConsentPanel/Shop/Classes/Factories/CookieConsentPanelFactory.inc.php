<?php
/*--------------------------------------------------------------------------------------------------
    CookieConsentPanelFactory.php 2019-12-19
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2019 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

/**
 * Class CookieConsentPanelFactory
 */
class CookieConsentPanelFactory implements CookieConsentPanelFactoryInterface
{
    
    /**
     * @inheritDoc
     */
    public function createView(): ContentView
    {
        return MainFactory::create(CookieConsentPanelContentView::class, $this->createManager());
    }
    
    
    /**
     * @return mixed|void
     */
    public function createManager(): CookieConsentManagerInterface
    {
        return CookieConsentManager::getInstance();
    }
}