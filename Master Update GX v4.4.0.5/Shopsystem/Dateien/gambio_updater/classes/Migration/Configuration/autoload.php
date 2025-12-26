<?php

use Doctrine\DBAL\Connection;
use Gambio\Core\Configuration\Migration\ConfigurationMigrationAssistant;
use Gambio\Core\Configuration\Migration\ConfigurationMigrations;
use Gambio\Core\Application\ServiceProviders\DoctrineQbServiceProvider;
use League\Container\Container;

require_once __DIR__ . '/../../../../vendor/autoload.php';

require_once __DIR__ . '/ConfigurationMigration.php';
require_once __DIR__ . '/DoctrineConnectionServiceProvider.php';
require_once __DIR__ . '/Tables/ConfigurationStorageTableMigration.php';
require_once __DIR__ . '/Tables/ConfigurationTableMigration.php';
require_once __DIR__ . '/Tables/GmConfigurationTableMigration.php';
require_once __DIR__ . '/Tables/GmContentsTableMigration.php';
require_once __DIR__ . '/Additional/MetaTagsFix.php';
require_once __DIR__ . '/Additional/MailbeezMigration.php';
require_once __DIR__ . '/ConfigurationMigrationAssistant.php';
require_once __DIR__ . '/ConfigurationMigrationMapping.php';
require_once __DIR__ . '/ConfigurationMigrations.php';
