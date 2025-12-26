<?php
/* --------------------------------------------------------------
 OrderStatus.php 2020-01-06
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 06 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

namespace Gambio\Core\Configuration\Types;

use Gambio\Core\Configuration\Models\Read\Collections\Options;
use Gambio\Core\Configuration\Models\Read\Option;
use Gambio\Core\Configuration\Repositories\Components\OptionsResolver;

class OrderStatus implements ConfigurationType
{
    /**
     * @inheritDoc
     */
    public function toOptions(OptionsResolver $resolver, string $value = null): ?Options
    {
        $connection = $resolver->connection();
        $qb         = $connection->createQueryBuilder();
        
        $text     = Option::TEXT;
        $valueCol = Option::VALUE;
        
        $data = $qb->select("orders_status_id as `$valueCol`, orders_status_name as `$text`")
                   ->from('orders_status')
                   ->where('language_id = ' . $resolver->languageId())
                   ->orderBy('orders_status_name')
                   ->execute()
                   ->fetchAll();
        
        return Options::fromArray($data);
    }
    
    
    /**
     * @inheritDoc
     */
    public function inputType(): string
    {
        return 'dropdown';
    }
}