<?php
/* --------------------------------------------------------------
   DefaultServerConfiguration.php 2021-01-08
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\Application\Bootstrapper;

use Gambio\Core\Application\Contracts\Application;
use Gambio\Core\Application\Contracts\Bootstrapper;
use Gambio\Core\Configuration\ConfigurationService;

/**
 * Class SetDefaultServerConfiguration
 *
 * @package Gambio\Core\Application\Bootstrapper
 */
class DefaultServerConfiguration implements Bootstrapper
{
    /**
     * @inheritDoc
     */
    public function boot(Application $application): void
    {
        $this->setDefaultErrorReporting(E_ALL & ~E_NOTICE & ~E_USER_NOTICE);
        $this->setDefaultTimezone($application, 'Europe/Berlin');
        $this->setDefaultMemoryLimit(128);
    }
    
    
    /**
     * @param int $errorReporting
     */
    private function setDefaultErrorReporting(int $errorReporting): void
    {
        @error_reporting($errorReporting);
    }
    
    
    /**
     * @param Application $application
     * @param string      $timeZone
     */
    private function setDefaultTimezone(Application $application, string $timeZone): void
    {
        /** @var ConfigurationService $configurationService */
        $configurationService = $application->get(ConfigurationService::class);
        
        $defaultTimezone = $configurationService->find('configuration/DATE_TIMEZONE');
        $defaultTimezone = ($defaultTimezone !== null) ? $defaultTimezone->value() : $timeZone;
        
        @date_default_timezone_set($defaultTimezone);
    }
    
    
    /**
     * @param int $limitInMegaBytes
     */
    private function setDefaultMemoryLimit(int $limitInMegaBytes): void
    {
        $minMemoryLimit = $limitInMegaBytes . 'M';
        
        if (function_exists('ini_get') && function_exists('ini_set')) {
            $serverMemoryLimit = @ini_get('memory_limit');
            
            if (preg_match('/([\d]+)([MG]*)/', $serverMemoryLimit, $matches)) {
                $memoryLimit = (int)$matches[1];
                if (isset($matches[2]) && $matches[2] === 'G') {
                    $memoryLimit *= 1024;
                } elseif (isset($matches[2]) && $matches[2] !== 'M') {
                    $memoryLimit *= 1024 * 1024;
                }
                
                if ($memoryLimit < $limitInMegaBytes) {
                    @ini_set('memory_limit', $minMemoryLimit);
                }
            } elseif (preg_match('/^[\d]+$/', $serverMemoryLimit)) {
                $memoryLimit    = (int)$serverMemoryLimit;
                $minMemoryLimit = $limitInMegaBytes * 1024 * 1024;
                
                if ($memoryLimit < $minMemoryLimit) {
                    @ini_set('memory_limit', $minMemoryLimit);
                }
            }
        }
    }
}