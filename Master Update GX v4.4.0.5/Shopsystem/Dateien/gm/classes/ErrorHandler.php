<?php
/* --------------------------------------------------------------
   ErrorHandler.php 2020-08-24
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

use Gambio\Core\Logging\Builder\TextAndJsonLoggerBuilder;
use Gambio\Core\Logging\LoggerBuilder;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once DIR_FS_CATALOG . 'system/classes/error_handling/UserFriendlyErrorHandler.inc.php';

/**
 * Class ErrorHandler
 */
class ErrorHandler
{
    /**
     * @var LoggerBuilder
     */
    protected $loggerBuilder;
    
    
    /**
     * ErrorHandler constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        // don't display php errors
        $displayErrors = file_exists(__DIR__ . '/../../.dev-environment') ? '1' : '0';
        ini_set('display_errors', $displayErrors);
        
        // but report all php errors
        error_reporting(E_ALL & ~E_NOTICE & ~E_USER_NOTICE);
        ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_USER_NOTICE);
    }
    
    
    /**
     * Handles occurring PHP errors
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     * @param array  $errcontext
     *
     * @return bool
     */
    public function HandleError(int $errno, string $errstr, string $errfile, int $errline, array $errcontext): bool
    {
        // don't handle errors while executing unit tests
        if (defined('UNIT_TEST_RUNNING') && UNIT_TEST_RUNNING === true) {
            return false;
        }
        
        // stop error handling here, if error should not be reported
        if ((bool)(error_reporting() & $errno) === false) {
            return true;
        }
        
        // handle fatal errors (show user friendly error page and set http status to 500)
        if ($errno === E_ERROR || $errno === E_PARSE
            || $errno === E_CORE_ERROR
            || $errno === E_COMPILE_ERROR
            || $errno === E_RECOVERABLE_ERROR
            || $errno === E_USER_ERROR) {
            if (function_exists('http_response_code')) {
                http_response_code(500);
            }
            
            if (UserFriendlyErrorHandler::isActive()) {
                $errorCode = UserFriendlyErrorHandler::handleError($errno, $errstr, $errfile, $errline, $errcontext);
                $this->getLoggerBuilder()->omitRequestData()->build()->info('Showed user friendly error page: '
                                                                            . $errorCode,
                                                                            ['errorCode' => $errorCode]);
            }
        }
        
        // trim error message if it contains more than one lines
        $errorMessage = strpos($errstr, PHP_EOL) !== false ? substr($errstr, 0, strpos($errstr, PHP_EOL)) : $errstr;
        
        // log error information
        switch ($errno) {
            // currently we are not handling notices, because there are too many :(
            case E_USER_NOTICE:
            case E_NOTICE:
                //$this->getLogger()->omitRequestData()->build()->notice($errstr, [
                //    'message' => $errstr,
                //    'code' => $errno,
                //    'file' => $errfile,
                //    'line' => $errline,
                //    'context' => $errcontext,
                //    'obCleanCache' => $obCleanCache ?? null,
                //]);
                //
                //$this->flushOutputBuffer();
                
                break;
            
            case E_ERROR:
                if (class_exists('DataCache', false)) {
                    // clear registry cache for fatal errors caused by deleted overload class files
                    $dataCache = DataCache::get_instance();
                    $dataCache->clear_cache('MainFactory-backend');
                    $dataCache->clear_cache('MainFactory-frontend');
                    $dataCache->clear_cache('MainFactory-create');
                    $dataCache->clear_cache('MainFactory-load');
                    $dataCache->clear_cache('MainFactory-loadOrigin');
                    $dataCache->clear_cache('ClassFinder');
                    $dataCache->clear_cache('ClassRegistry');
                    $dataCache->clear_cache('directory_cache_template_blank');
                    if (defined('CURRENT_TEMPLATE')) {
                        $dataCache->clear_cache('directory_cache_' . StaticGXCoreLoader::getThemeControl()
                                                    ->getCurrentTheme());
                    }
                }
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_RECOVERABLE_ERROR:
                $obCleanCache = ob_get_clean();
            case E_USER_ERROR:
                $this->getLoggerBuilder()->addRequestData()->build()->error($errorMessage,
                                                                            [
                                                                                'message'      => $errstr,
                                                                                'code'         => $errno,
                                                                                'file'         => $errfile,
                                                                                'line'         => $errline,
                                                                                'context'      => $errcontext,
                                                                                'obCleanCache' => $obCleanCache ?? null,
                                                                            ]);
                
                $this->flushOutputBuffer();
                
                if (file_exists(__DIR__ . '/../../.dev-environment')) {
                    echo '<pre>' . $errstr . '</pre>';
                }
                
                die();
                break;
            
            case E_COMPILE_WARNING:
            case E_WARNING:
            case E_USER_WARNING:
            case E_CORE_WARNING:
            case E_STRICT:
            case E_USER_DEPRECATED:
            case E_DEPRECATED:
            default:
                $this->getLoggerBuilder()->omitRequestData()->build()->warning($errorMessage,
                                                                               [
                                                                                   'message' => $errstr,
                                                                                   'code'    => $errno,
                                                                                   'file'    => $errfile,
                                                                                   'line'    => $errline,
                                                                                   'context' => $errcontext,
                                                                               ]);
                
                $this->flushOutputBuffer();
        }
        
        return true;
    }
    
    
    /**
     * Flushes the output buffer, if there is an active buffer.
     */
    private function flushOutputBuffer(): void
    {
        if (ob_get_contents() !== '') {
            if (headers_sent() === false) {
                @ini_set('zlib.output_compression', 'Off');
            }
            @ob_end_flush();
        }
    }
    
    
    /**
     * PHP shutdown handler function.
     */
    public function shutdown(): void
    {
        $latestError = error_get_last();
        if (isset($latestError)) {
            $this->HandleError($latestError['type'],
                               $latestError['message'],
                               $latestError['file'],
                               $latestError['line'],
                               []);
        }
    }
    
    
    /**
     * Return logger builder
     *
     * @return LoggerBuilder
     */
    private function getLoggerBuilder(): LoggerBuilder
    {
        if ($this->loggerBuilder === null) {
            $this->loggerBuilder = new TextAndJsonLoggerBuilder('php-error-handler');
        }
        
        return $this->loggerBuilder;
    }
}
