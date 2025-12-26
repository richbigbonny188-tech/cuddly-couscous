<?php
/* --------------------------------------------------------------
 LoadAdminShopConfiguration.php 2020-03-20
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Api\Application\Kernel\Bootstrapper;

use Gambio\Core\Application\Contracts\Application;
use Gambio\Core\Application\Contracts\Bootstrapper;
use Gambio\Core\Application\ValueObjects\Path;
use Gambio\Core\Application\ValueObjects\Server;
use Gambio\Core\Application\ValueObjects\Url;
use UnexpectedValueException;
use function array_key_exists;
use function file_exists;

/**
 * Class LoadAdminShopConfiguration
 *
 * @package Gambio\Api\Application\Kernel\Bootstrapper
 */
class LoadAdminShopConfiguration implements Bootstrapper
{
    /**
     * @inheritDoc
     */
    public function boot(Application $application): void
    {
        $paths = $this->determineApplicationPaths();
        
        $frontendPath = $paths['frontendPath'];
        $webPath      = $paths['webPath'];
        
        $configureContent = $this->configurationContent($frontendPath);
        $host             = $this->determineHost($configureContent);
        $sslEnabled       = $this->determineSslEnabled($configureContent);
        $requestUri       = $_SERVER['REQUEST_URI'];
        
        $application->registerShared(Path::class)->addArgument($frontendPath);
        $application->registerShared(Url::class)->addArgument($host)->addArgument($webPath);
        $application->registerShared(Server::class)->addArgument($sslEnabled)->addArgument($requestUri);
    }
    
    
    /**
     * Returns the content of the configure.php file.
     *
     * @param string $frontendPath
     *
     * @return string
     */
    private function configurationContent(string $frontendPath): string
    {
        $configureFile = "{$frontendPath}/admin/includes/configure.php";
        
        if (!file_exists($configureFile)) {
            throw new UnexpectedValueException("Configuration file not found ({$configureFile}).");
        }
        
        return file_get_contents($configureFile);
    }
    
    
    /**
     * Determines the host name.
     * The $configureData variable should contain the content of the configure.php file.
     *
     * @param string $configureData
     *
     * @return string
     */
    private function determineHost(string $configureData): string
    {
        $regex = '/define\(\'HTTP_SERVER\', \'(.*)\'\);/';
        preg_match($regex, $configureData, $matches);
        
        if (!array_key_exists(1, $matches)) {
            throw new UnexpectedValueException("Couldn't find HTTP_SERVER configuration value.");
        }
        
        return $matches[1];
    }
    
    
    /**
     * Determines the host name.
     * The $configureData variable should contain the content of the configure.php file.
     *
     * @param string $configureData
     *
     * @return bool
     */
    private function determineSslEnabled(string $configureData): bool
    {
        $regex = '/define\(\'ENABLE_SSL_CATALOG\', \'(.*)\'\);/';
        preg_match($regex, $configureData, $matches);
        
        if (!array_key_exists(1, $matches)) {
            throw new UnexpectedValueException("Couldn't find ENABLE_SSL_CATALOG configuration value.");
        }
        
        return strtolower($matches[1]) === 'true';
    }
    
    
    /**
     * Determines application paths.
     *
     * This function returns an array containing the filesystems frontend- and backend paths.
     * Additionally, it returns the web path which is used after the hostname of the shops url.
     *
     * @return array
     */
    private function determineApplicationPaths(): array
    {
        if (isset($_SERVER['DOCUMENT_ROOT'])) {
            $documentRoot = $_SERVER['DOCUMENT_ROOT'] . '/';
        } elseif (!isset($_SERVER['DOCUMENT_ROOT']) && isset($_SERVER['SCRIPT_FILENAME'])
                  && isset($_SERVER['SCRIPT_NAME'])) {
            $documentRoot = substr($_SERVER['SCRIPT_FILENAME'], 0, -strlen($_SERVER['SCRIPT_NAME'])) . '/';
        } else {
            $documentRoot = '/var/www/html/'; // absolute server path required (domain root)
        }
        
        if (realpath($documentRoot) !== false) {
            $documentRoot = realpath($documentRoot) . '/';
        }
        
        $documentRoot = str_replace('\\', '/', $documentRoot);
        
        if ($documentRoot === '//') {
            $documentRoot = '/';
        }
        
        $frontendPath = dirname(__DIR__, 4);
        
        $frontendPath = str_replace('\\', '/', $frontendPath);
        $webPath      = substr($frontendPath, strlen($documentRoot) - 1);
        
        return [
            'frontendPath' => $frontendPath,
            'webPath'      => $webPath
        ];
    }
}