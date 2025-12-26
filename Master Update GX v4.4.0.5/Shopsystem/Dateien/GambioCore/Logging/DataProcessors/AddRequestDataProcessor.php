<?php
/* --------------------------------------------------------------
   AddRequestDataProcessor.php 2021-05-04
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\Logging\DataProcessors;

use Monolog\Processor\ProcessorInterface;

/**
 * Class AddRequestDataProcessor
 *
 * @package Gambio\Core\Logger
 */
class AddRequestDataProcessor implements ProcessorInterface
{
    /**
     * @param array $recordData
     *
     * @return array
     */
    public function __invoke(array $recordData): array
    {
        if (isset($_SERVER)) {
            $this->appendRequestData($recordData);
        }
        if (isset($_POST)) {
            $this->appendPostData($recordData);
        }
        if (isset($_GET)) {
            $this->appendQueryData($recordData);
        }
        if (isset($_SESSION)) {
            $this->appendSessionData($recordData);
        }
        
        return $recordData;
    }
    
    
    /**
     * Appends some request data to the provided record.
     *
     * @param array $record
     */
    private function appendRequestData(array &$record): void
    {
        $record['extra']['request']['method']    = $_SERVER['REQUEST_METHOD'] ?? null;
        $record['extra']['request']['uri']       = $_SERVER['REQUEST_URI'] ?? null;
        $record['extra']['request']['software']  = $_SERVER['SERVER_SOFTWARE'] ?? null;
        $record['extra']['request']['address']   = $_SERVER['SERVER_ADDR'] ?? null;
        $record['extra']['request']['userAgent'] = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $record['extra']['request']['time'] = null;
        if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $record['extra']['request']['time'] = round((int)(microtime(true) * 1000)
                                                        - (int)($_SERVER['REQUEST_TIME_FLOAT'] * 1000));
        } elseif (isset($_SERVER['REQUEST_TIME'])) {
            $record['extra']['request']['time'] = round((int)microtime(true) - $_SERVER['REQUEST_TIME']);
        }
        
        $record['extra']['request']['remoteAddress'] = null;
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $record['extra']['request']['remoteAddress'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $record['extra']['request']['remoteAddress'] = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $record['extra']['request']['remoteAddress'] = $_SERVER['REMOTE_ADDR'];
        }

        $record['extra']['request']['remoteAddress'] = $record['extra']['request']['remoteAddress'] === null 
            ? $record['extra']['request']['remoteAddress'] 
            : md5($record['extra']['request']['remoteAddress']);
    }
    
    
    /**
     * Appends the censored POST data to the provided record.
     *
     * @param array $record
     */
    private function appendPostData(array &$record): void
    {
        foreach ($_POST as $key => $value) {
            $record['extra']['post'][$key] = $value;
            if (in_array($key, ['password', 'confirmation', 'FTP_PASSWORD'])) {
                $record['extra']['post'][$key] = '*****';
            }
        }
    }
    
    
    /**
     * Appends the censored query (GET) data to the provided record.
     *
     * @param array $record
     */
    private function appendQueryData(array &$record): void
    {
        foreach ($_GET as $key => $value) {
            $record['extra']['query'][$key] = $value;
            if (in_array($key, ['password', 'confirmation', 'FTP_PASSWORD'])) {
                $record['extra']['query'][$key] = '*****';
            }
        }
    }
    
    
    /**
     * Appends the censored query (GET) data to the provided record.
     *
     * @param array $record
     */
    private function appendSessionData(array &$record): void
    {
        $record['extra']['session']['tpl']              = $_SESSION['tpl'] ?? null;
        $record['extra']['session']['language']         = $_SESSION['language'] ?? null;
        $record['extra']['session']['languages_id']     = $_SESSION['languages_id'] ?? null;
        $record['extra']['session']['language_charset'] = $_SESSION['language_charset'] ?? null;
        $record['extra']['session']['language_code']    = $_SESSION['language_code'] ?? null;
        $record['extra']['session']['currency']         = $_SESSION['currency'] ?? null;
        $record['extra']['session']['customers_status'] = $_SESSION['customers_status'] ?? null;
        $record['extra']['session']['customer_id']      = $_SESSION['customer_id'] ?? null;
        $record['extra']['session']['payment']          = $_SESSION['payment'] ?? null;
        $record['extra']['session']['shipping']         = $_SESSION['shipping'] ?? null;
        $record['extra']['session']['cartID']           = $_SESSION['cartID'] ?? null;
        $record['extra']['session']['sendto']           = $_SESSION['sendto'] ?? null;
        $record['extra']['session']['billto']           = $_SESSION['billto'] ?? null;
    }
}