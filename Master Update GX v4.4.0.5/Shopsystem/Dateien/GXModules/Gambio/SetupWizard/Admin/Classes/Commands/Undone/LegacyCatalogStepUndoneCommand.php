<?php
/*------------------------------------------------------------------------------
 LegacySetupWizardStepUndoneCommand.php 2020-08-18
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

/**
 * Class LegacySetupWizardStepUndoneCommand
 */
class LegacyCatalogStepUndoneCommand extends AbstractLegacyStepUndoneCommand
{
    /**
     * LegacySetupWizardStepUndoneCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('SETUP_WIZARD_STEP_CATALOG');
    }
    
}