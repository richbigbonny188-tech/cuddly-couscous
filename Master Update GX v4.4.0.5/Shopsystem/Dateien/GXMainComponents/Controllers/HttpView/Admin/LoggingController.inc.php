<?php
/* --------------------------------------------------------------
   LoggingController.inc.php 2020-01-24
   Gambio GmbH
   http://www.gambio.de
   Copyright © 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class LoggingController
 */
class LoggingController extends AdminHttpViewController
{
    /**
     * @return AdminLayoutHttpControllerResponse
     */
    public function actionDefault(): AdminLayoutHttpControllerResponse
    {
        $languageTextManager = MainFactory::create('LanguageTextManager', 'logging', $_SESSION['languages_id']);
        
        $title = new NonEmptyStringType($languageTextManager->get_text('title'));
        
        $template = new ExistingFile(new NonEmptyStringType(DIR_FS_ADMIN . '/html/content/logging/overview.html'));
        
        $assets = MainFactory::create('AssetCollection');
        $assets->add(MainFactory::create('Asset', 'admin_buttons.lang.inc.php'));
        $assets->add(MainFactory::create('Asset', 'logging.lang.inc.php'));
        
        $tabNavigation = MainFactory::create('ContentNavigationCollection', []);
        $tabNavigation->add(new StringType($languageTextManager->get_text('tabs_new_logging')),
                            new StringType('admin.php?do=Logging'),
                            new BoolType(true));
        $tabNavigation->add(new StringType($languageTextManager->get_text('tabs_old_logging')),
                            new StringType('show_logs.php'),
                            new BoolType(false));
        
        return MainFactory::create('AdminLayoutHttpControllerResponse',
                                   $title,
                                   $template,
                                   null,
                                   $assets,
                                   $tabNavigation);
    }
    
    
    /**
     * @return JsonHttpControllerResponse
     */
    public function actionGetLogfiles(): JsonHttpControllerResponse
    {
        $response = [];
        $logFiles = glob(DIR_FS_CATALOG . 'logfiles/*.log.json');
        usort($logFiles,
            function ($a, $b) {
                return filemtime($a) < filemtime($b);
            });
        $logFiles = array_map('basename', $logFiles);
        foreach ($logFiles as $logFile) {
            $name              = str_replace(['_', '.'], [' ', ' - '], substr($logFile, 0, -9));
            $response[$name]   = array_map('basename', glob(DIR_FS_CATALOG . 'logfiles/' . $logFile . '.*.gz'));
            $response[$name][] = $logFile;
            $response[$name]   = array_reverse($response[$name]);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', ['logfiles' => $response]);
    }
    
    
    /**
     * @return JsonHttpControllerResponse
     */
    public function actionGetLogs(): JsonHttpControllerResponse
    {
        $logfile = DIR_FS_CATALOG . 'logfiles/' . $this->_getQueryParameter('logfile');
        if (file_exists($logfile) && fnmatch('*.log.json', $logfile)) {
            $logs = file($logfile);
        } elseif (file_exists($logfile) && fnmatch('*.log.json.*.gz', $logfile)) {
            $logs = explode(PHP_EOL, gzdecode(file_get_contents($logfile)));
            if ($logs[count($logs) - 1] === '') {
                unset($logs[count($logs) - 1]);
            }
        }
    
        $logs = array_filter($logs ?? [], static function($log){
            return $log !== "null\n" && $log !== "null";
        });
        
        return MainFactory::create('JsonHttpControllerResponse', ['logs' => array_reverse($logs)]);
    }
    
    
    /**
     * @return JsonHttpControllerResponse
     */
    public function actionDeleteSelectedLogs(): JsonHttpControllerResponse
    {
        $selected = $this->_getPostData('selected');
        if ($selected === null) {
            return MainFactory::create('JsonHttpControllerResponse',
                                       ['success' => false, 'error' => 'Missing data "selected".']);
        }
        
        $success = false;
        $files   = array_merge(glob(DIR_FS_CATALOG . 'logfiles/' . $selected . '*'),
                               glob(DIR_FS_CATALOG . 'logfiles/' . str_replace('.log.json', '.log.txt', $selected)
                                    . '*'));
        foreach ($files as $file) {
            if (file_exists($file)) {
                $success = @unlink($file);
            }
        }
        
        return MainFactory::create('JsonHttpControllerResponse', ['success' => $success]);
    }
    
    
    /**
     * @return JsonHttpControllerResponse
     */
    public function actionDeleteAllLogfiles(): JsonHttpControllerResponse
    {
        $success  = true;
        $logfiles = array_merge(glob(DIR_FS_CATALOG . 'logfiles/*.log.json'),
                                glob(DIR_FS_CATALOG . 'logfiles/*.log.json.*.gz'),
                                glob(DIR_FS_CATALOG . 'logfiles/*.log.txt'),
                                glob(DIR_FS_CATALOG . 'logfiles/*.log.txt.*.gz'));
        foreach ($logfiles as $logfile) {
            $success = $success && @unlink($logfile);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', ['success' => $success]);
    }
    
    
    /**
     * @return JsonHttpControllerResponse
     */
    public function actionDeleteOldLogfiles(): JsonHttpControllerResponse
    {
        $days = (int)$this->_getPostData('days');
        if ($days < 1) {
            return MainFactory::create('JsonHttpControllerResponse',
                                       ['success' => false, 'error' => 'Invalid value for parameter "days".']);
        }
        
        $success  = true;
        $logfiles = array_merge(glob(DIR_FS_CATALOG . 'logfiles/*.log.json'),
                                glob(DIR_FS_CATALOG . 'logfiles/*.log.json.*.gz'),
                                glob(DIR_FS_CATALOG . 'logfiles/*.log.txt'),
                                glob(DIR_FS_CATALOG . 'logfiles/*.log.txt.*.gz'));
        foreach ($logfiles as $logfile) {
            if (filemtime($logfile) <= time() - $days * 60 * 60 * 24) {
                $success = $success && @unlink($logfile);
            }
        }
        
        return MainFactory::create('JsonHttpControllerResponse', ['success' => $success]);
    }
}