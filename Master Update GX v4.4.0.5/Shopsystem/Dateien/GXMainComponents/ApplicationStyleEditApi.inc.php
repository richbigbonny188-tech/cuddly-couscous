<?php
/* --------------------------------------------------------------
   ApplicationStyleEditApi.inc.php 2020-05-06
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2019 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

namespace Gambio\GX;
require_once __DIR__ . '/Application.inc.php';
require_once __DIR__.'/FakeSessionHandler.inc.php';

use MainFactory;
use StaticGXCoreLoader;

class ApplicationStyleEditApi extends Application
{

    protected function startSession()
    {
        // define how the session functions will be used
        require_once DIR_WS_FUNCTIONS . 'sessions.php';

        gm_set_session_parameters();
        unset($_GET[session_name()]);

        session_set_save_handler(new FakeSessionHandler() );

        session_start();
    }

    protected function setSessionObjects(){}

    protected function handlePageSpecificRequests() {}

    protected function setUpFrontend(){
        $currentTheme = StaticGXCoreLoader::getThemeControl()->getCurrentTheme();
        $GLOBALS['coo_template_control'] = MainFactory::create_object('TemplateControl', [$currentTheme], true);
    }


}