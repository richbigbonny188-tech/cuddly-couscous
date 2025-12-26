<?php
/* --------------------------------------------------------------
  SetUpWizardDesignAndColorCompletionCheck.inc.php 2020-08-19
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

/**
 * Class SetUpWizardDesignAndColorCompletionCheck
 * @package GXModules\Gambio\SetupWizard\Admin\Overloads\AdminApplicationTopExtenderComponent
 */
class SetUpWizardDesignAndColorCompletionCheck extends SetUpWizardDesignAndColorCompletionCheck_parent
{
    public function proceed()
    {
        parent::proceed();
        
        $requestUri   = $_SERVER['REQUEST_URI'];
        $activeScript = explode('/', $requestUri);
        $activeScript = array_pop($activeScript);
        
        if ($activeScript === 'admin.php?do=TemplateConfiguration') {
            (new LegacyDesignStepDoneCommand)->execute();
        }
    }
}