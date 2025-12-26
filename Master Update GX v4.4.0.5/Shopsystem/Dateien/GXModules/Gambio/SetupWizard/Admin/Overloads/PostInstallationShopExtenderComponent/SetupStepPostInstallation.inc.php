<?php
/* --------------------------------------------------------------
  SetupStepPostInstallation.inc.php 2019-05-31
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2019 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

class SetupStepPostInstallation extends SetupStepPostInstallation_parent
{
    /**
     *
     */
    public function proceed()
    {
        parent::proceed();
        MainFactory::create(LegacyCatalogStepUndoneCommand::class)->execute();
        MainFactory::create(LegacyBasicSettingsStepDoneCommand::class)->execute();
    }
}