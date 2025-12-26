<?php
/* --------------------------------------------------------------
   ApplicationCss.inc.php 2020-06-04
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

namespace Gambio\GX;

require_once __DIR__ . '/Application.inc.php';

/**
 * Class ApplicationCss
 *
 * @package Gambio
 */
class ApplicationCss extends Application
{
    protected $error = '';


    public function run()
    {
        $this->runActions();
        $this->sendHeader();
    }


    public function runWithoutHeader()
    {
        $this->runActions();
    }


    public function errorHandler($errorNumber, $errorMessage, $errorFile, $errorLine)
    {
        if (!(error_reporting() & $errorNumber)) {
            return null; // This error code is not included in error_reporting.
        }

        $this->error .= str_replace('"', '\"', "[CSS compile error] '$errorMessage' in $errorFile:$errorLine ");

        echo '
		body:before {
			content: "' . $this->error . '";
			position: absolute;
			background: #FF5722;
			color: #fff;
			top: 0;
			left: 0;
			z-index: 100000;
			padding: 15px;
		}
	';

        return true; // Don't execute PHP internal error handler.
    }


    public function errorOccurred()
    {
        return $this->error !== '';
    }


    protected function runActions()
    {
        $this->runGProtector();

        self::loadConfig();

        $this->defineInitialConstants();
        $this->setMemoryLimit(256);
        $this->registerErrorHandler();
        $this->includeFunctions();
        $this->includeWrapperFunctions();
        $this->initGXEngine();
        $this->setTimezone();
        $this->registerAutoloader();
        $this->connectToDatabase();
        $this->defineConstantsFromDbConfigurationTable();
        $this->setCurrentTemplate();
        $this->setUpFrontend();
    }


    protected function registerErrorHandler()
    {
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_CORE_ERROR & ~E_CORE_WARNING);

        set_error_handler([$this, 'errorHandler']);
    }


    protected function sendHeader()
    {
        header('Content-Type: text/css; charset=utf-8');
    }
}