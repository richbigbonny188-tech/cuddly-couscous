<?php
/* --------------------------------------------------------------
 SetupWizardShippingModuleInstalled.php 2021-02-14
 Gambio GmbH
 http://www.gambio.de

 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

/**
 * Class SetupWizardShippingModuleInstalled
 */
class SetupWizardShippingModuleInstalled extends SetupWizardShippingModuleInstalled_parent
{
    public function proceed()
    {
        parent::proceed();
        
        $requestUri = $_SERVER['REQUEST_URI'];
        $activeScript = explode('/', $requestUri);
        $activeScript = array_pop($activeScript);
        
        $urlParts = explode('&', $activeScript);
        if ($_GET['set'] === 'shipping' && $_GET['action'] === 'install'
            && $urlParts[0] === 'modules.php?set=shipping') {
            (new LegacyShippingStepDoneCommand())->execute();
        }
    }
    
}