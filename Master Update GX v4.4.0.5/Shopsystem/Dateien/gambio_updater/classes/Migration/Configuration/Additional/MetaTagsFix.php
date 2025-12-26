<?php
/* --------------------------------------------------------------
 MetaTagsFix.php 2020-03-24
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Configuration\Migration\Additional;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Gambio\Core\Configuration\Migration\ConfigurationMigration;

/**
 * Class MetaTagsFix
 */
class MetaTagsFix implements ConfigurationMigration
{
    /**
     * @var Connection
     */
    private $connection;
    
    
    /**
     * MetaTagsFix constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    
    
    /**
     * Fixes meta tag entries.
     *
     * Meta tags was previously stored with group id = 1. The new system expect the id to be 9999.
     * The method selects all wrongly migrated meta tags and fix them.
     */
    public function migrate(): void
    {
        $qb     = $this->connection->createQueryBuilder();
        $result = $qb->select('*')
            ->from('gx_configurations')
            ->where('legacy_group_id = 1')
            ->andWhere('language_id IS NOT NULL')
            ->andWhere("{$this->connection->quoteIdentifier('key')} LIKE 'gm%'")
            ->execute()
            ->fetchAll();
        
        foreach ($result as $dataset) {
            try {
                $this->connection->update('gx_configurations',
                                          ['legacy_group_id' => 9999],
                                          ['id' => $dataset['id']]);
            } catch (DBALException $e) {
            }
        }
    }
}