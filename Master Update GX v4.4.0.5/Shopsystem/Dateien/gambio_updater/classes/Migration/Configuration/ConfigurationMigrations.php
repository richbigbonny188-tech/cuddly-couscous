<?php
/* --------------------------------------------------------------
 ConfigurationMigrations.php 2020-01-16
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 16 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Configuration\Migration;

use Doctrine\DBAL\Connection;
use Gambio\Core\Configuration\Migration\Additional\MailbeezMigration;
use Gambio\Core\Configuration\Migration\Additional\MetaTagsFix;
use Gambio\Core\Configuration\Migration\Tables\ConfigurationStorageTableMigration;
use Gambio\Core\Configuration\Migration\Tables\ConfigurationTableMigration;
use Gambio\Core\Configuration\Migration\Tables\GmConfigurationTableMigration;
use Gambio\Core\Configuration\Migration\Tables\GmContentsTableMigration;

/**
 * Class ConfigurationMigrations
 * @package Gambio\Core\Configuration\Migration
 */
class ConfigurationMigrations implements ConfigurationMigration
{
    /**
     * @var ConfigurationMigration[]
     */
    private $migrations = [];
    
    
    /**
     * ConfigurationMigrations constructor.
     *
     * @param Connection                      $connection
     * @param ConfigurationMigrationAssistant $assistant
     */
    public function __construct(Connection $connection, ConfigurationMigrationAssistant $assistant)
    {
        $this->migrations[] = new ConfigurationTableMigration($connection, $assistant);
        $this->migrations[] = new GmConfigurationTableMigration($connection, $assistant);
        $this->migrations[] = new GmContentsTableMigration($connection, $assistant);
        $this->migrations[] = new ConfigurationStorageTableMigration($connection, $assistant);
        
        $this->migrations[] = new MetaTagsFix($connection);
        $this->migrations[] = new MailbeezMigration($connection, $assistant);
    }
    
    
    /**
     * Migrates data of configuration, gm_configuration, gm_contents and configuration storage
     * to gx_configuration, respectively.
     */
    public function migrate()
    {
        foreach ($this->migrations as $migration) {
            $migration->migrate();
        }
    }
}