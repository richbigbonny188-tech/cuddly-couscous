<?php
/* --------------------------------------------------------------
 LoggerFactory.php 2020-09-07
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Logging;

use Gambio\Core\Application\Contracts\Application;
use Psr\Log\LoggerInterface;

/**
 * Class LoggerFactory
 * @package Gambio\Core\Logging
 */
class LoggerFactory
{
    /**
     * @var Application|null
     */
    private static $application;
    
    
    /**
     * Creates a new logger instance.
     *
     * @param string $namespace
     * @param bool   $addRequestData
     *
     * @return LoggerInterface
     */
    public static function create($namespace = 'general', bool $addRequestData = false): LoggerInterface
    {
        $builder = static::getLoggerBuilder();
        
        $builder->changeNamespace($namespace);
        $addRequestData ? $builder->addRequestData() : $builder->omitRequestData();
        
        return $builder->build();
    }
    
    
    /**
     * Registers the application instance.
     *
     * Its required to get access to the logger instance.
     * This step should be done in a bootable service provider.
     *
     * @param Application $application
     */
    public static function registerApplication(Application $application): void
    {
        static::$application = $application;
    }
    
    
    /**
     * Returns the logger builder or create a new one.
     *
     * This method could raise a fatal error (if the static::$application member is not set), but we accept
     * to not throw any exception if this is the cause to improve IDE experience.
     * (Otherwise it would be required to place "throws" annotations everytime the logger is used).
     *
     * If this error happen, its likely that ::registerApplication was not called previously.
     *
     * @return LoggerBuilder
     */
    private static function getLoggerBuilder(): LoggerBuilder
    {
        return static::$application->get(LoggerBuilder::class);
    }
}