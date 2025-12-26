<?php
/* --------------------------------------------------------------
 GroupPermissionListener.php 2020-03-05
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Configuration\EventListeners;

use Doctrine\DBAL\Connection;
use Gambio\Core\Configuration\ConfigurationService;
use Gambio\Core\Configuration\Events\GroupCheckUpdated;
use Gambio\Core\Configuration\Repositories\Components\ConfigurationWriter;

/**
 * Class GroupPermissionListener
 * @package Gambio\Core\Configuration\EventListeners
 *
 * @codeCoverageIgnore
 */
class GroupPermissionListener
{
    /**
     * @var ConfigurationService
     */
    private $configurationService;
    
    /**
     * @var Connection
     */
    private $connection;
    
    
    /**
     * ProductsGroupCheckListener constructor.
     *
     * @param ConfigurationService $configurationService
     * @param Connection           $connection
     */
    public function __construct(ConfigurationService $configurationService, Connection $connection)
    {
        $this->configurationService = $configurationService;
        $this->connection           = $connection;
    }
    
    
    /**
     * @param GroupCheckUpdated $event
     *
     * @return GroupCheckUpdated
     */
    public function __invoke(GroupCheckUpdated $event): GroupCheckUpdated
    {
        if ($this->notChanged($event)) {
            return $event;
        }
        
        $customerStatusIds = $this->customerStatusIds();
        $mode              = strtolower($event->newValue()) === 'true';
        
        $this->update($mode, $customerStatusIds);
        
        return $event;
    }
    
    
    /**
     * Updates the products, categories and content manager tables with new group permission data.
     *
     * @param bool  $mode
     * @param array $customerStatusIds
     */
    private function update(bool $mode, array $customerStatusIds): void
    {
        $newStatus = $mode ? '1' : '0';
        
        foreach ($customerStatusIds as $customerStatusId) {
            $this->updatePermissions('products', $customerStatusId, $newStatus);
            $this->updatePermissions('categories', $customerStatusId, $newStatus);
        }
        $this->updateContentManager($mode, $customerStatusIds);
    }
    
    
    /**
     * Updates group permission column for given table.
     *
     * @param string $table
     * @param string $customerStatusId
     * @param string $status
     */
    private function updatePermissions(string $table, string $customerStatusId, string $status): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->update($table)->set("group_permission_{$customerStatusId}", $qb->createNamedParameter($status))->execute();
    }
    
    
    /**
     * Updates the content manager table group permission data.
     *
     * @param bool  $mode
     * @param array $customerStatusIds
     */
    private function updateContentManager(bool $mode, array $customerStatusIds): void
    {
        $keys    = array_map(
            static function (string $element): string {
                return "c_{$element}_group";
            },
            $customerStatusIds
        );
        $keysStr = $mode ? implode(',', $keys) : '';
        
        $qb = $this->connection->createQueryBuilder();
        $qb->update('content_manager')->set('group_ids', $qb->createNamedParameter($keysStr))->execute();
    }
    
    
    /**
     * Returns all available customer status ids.
     *
     * @return array
     */
    private function customerStatusIds(): array
    {
        $result = $this->connection->fetchAll('SELECT DISTINCT `customers_status_id` FROM `customers_status`;');
        $ids    = [];
        foreach ($result as $dataset) {
            $ids[] = $dataset['customers_status_id'];
        }
        
        return $ids;
    }
    
    
    /**
     * Checks if the value changed.
     * If the value is not changed, we dont want to update the permissions.
     *
     * @param GroupCheckUpdated $event
     *
     * @return bool
     */
    private function notChanged(GroupCheckUpdated $event): bool
    {
        $oldConfig = $this->configurationService->find(ConfigurationWriter::KEY_GROUP_CHECK);
        if ($oldConfig) {
            return $oldConfig->value() === $event->newValue();
        }
        
        // no update of group permissions
        return true;
    }
}