<?php
/* --------------------------------------------------------------
 DownloadOrderStatus.php 2020-02-04
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Configuration\Types;

use Gambio\Core\Configuration\Models\Read\Collections\Options;
use Gambio\Core\Configuration\Repositories\Components\OptionsResolver;
use function explode;
use function in_array;

/**
 * Class DownloadOrderStatus
 * @package Gambio\Core\Configuration\Types
 */
class DownloadOrderStatus implements ConfigurationType
{
    /**
     * @inheritDoc
     */
    public function toOptions(OptionsResolver $resolver, string $value = null): ?Options
    {
        $connection = $resolver->connection();
        $key        = $connection->quoteIdentifier('key');
        $valueCol   = $connection->quoteIdentifier('value');
        
        $qb    = $connection->createQueryBuilder();
        $where = "language_id = {$qb->createNamedParameter($resolver->languageId())}";
        
        $orderStatusData = $qb->select('orders_status_id as value, orders_status_name as text')
                              ->from('orders_status')
                              ->where($where)
                              ->orderBy('orders_status_id')
                              ->execute()
                              ->fetchAll();
        
        $qb    = $connection->createQueryBuilder();
        $where = "{$key} = 'configuration/DOWNLOAD_MIN_ORDERS_STATUS'";
        
        $downloadOrderStatusResult = $qb->select($valueCol)->from('gx_configurations')->where($where)->execute()->fetch(
        );
        $activeOrderStatus         = explode('|', $downloadOrderStatusResult['value']);
        
        foreach ($orderStatusData as &$orderStatus) {
            $orderStatus['active'] = in_array($orderStatus['value'], $activeOrderStatus, true);
        }
        
        return Options::fromArray($orderStatusData);
    }
    
    
    /**
     * @inheritDoc
     */
    public function inputType(): string
    {
        return 'multi-switcher';
    }
}