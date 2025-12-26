<?php
/* --------------------------------------------------------------
 FooterBadgesLoader.php 2020-04-16
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

namespace GXModules\Gambio\Google\Admin\Module;

use Doctrine\DBAL\Connection;
use Gambio\Core\TemplateEngine\LayoutData;
use Gambio\Core\TemplateEngine\Loader;

/**
 * Class FooterBadgesLoader
 * @package Gambio\Admin\Layout\Smarty\Loaders
 */
class GoogleFooterBadgeLoader implements Loader
{
    /**
     * @var Connection
     */
    private $connection;
    
    
    /**
     * FooterBadgesLoader constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    
    
    /**
     * @inheritDoc
     */
    public function load(LayoutData $data): void
    {
        $data->assign('isConnectedWithGoogle', $this->isConnected());
    }
    
    
    /**
     * Determines the google connection status.
     *
     * @return bool
     */
    protected function isConnected(): bool
    {
        $qb = $this->connection->createQueryBuilder();
        
        $optionEqConnectionStatus = "{$this->connection->quoteIdentifier('option')} = {$qb->createNamedParameter('connection-status')}";
        $scopeEqGeneral           = "{$this->connection->quoteIdentifier('scope')} = {$qb->createNamedParameter('general')}";
        
        $result = $qb->select($this->connection->quoteIdentifier('value'))->from('google_configurations')->where(
            $optionEqConnectionStatus
        )->andWhere($scopeEqGeneral)->execute()->fetch();
        
        if (!$result || !array_key_exists('value', $result)) {
            return false;
        }
        $parsedResult = json_decode($result['value'], true);
        
        return !array_key_exists('value', $parsedResult) ? false : (bool)$parsedResult['value'];
    }
}