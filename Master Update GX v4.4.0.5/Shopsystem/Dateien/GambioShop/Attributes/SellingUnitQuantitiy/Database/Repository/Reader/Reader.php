<?php
/**
 * Reader.php 2021-01-08
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2020 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

namespace Gambio\Shop\Attributes\SellingUnitQuantitiy\Database\Repository\Reader;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Gambio\Shop\Attributes\ProductModifiers\Database\ValueObjects\AttributeModifierIdentifier;
use Gambio\Shop\Attributes\SellingUnitModel\Database\Repository\DTO\AttributesModelDto;
use Gambio\Shop\Attributes\SellingUnitQuantitiy\Database\Repository\DTO\AttributeInfo;

/**
 * Class Reader
 * @package Gambio\Shop\Attributes\SellingUnitQuantitiy\Database\Repository\Reader
 */
class Reader implements ReaderInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * Reader constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function getQuantity(int $productId, int $modifierIdentifierId): AttributeInfo
    {
        $records = $this->connection
            ->createQueryBuilder()
            ->select('attributes_stock')
            ->from('products_attributes')
            ->where('products_id = :products_id ')
            ->andWhere('options_values_id = :options_values_id')
            ->setParameter('products_id',$productId)
            ->setParameter('options_values_id', $modifierIdentifierId)
            ->execute()
            ->fetchAll();
            
        if(count($records)){
            return new AttributeInfo((float)$records[0]['attributes_stock']);
        }

        return new AttributeInfo((float)0);
    }
    
    
    /**
     * @param int $productId
     * @param int $modifierIdentifierId
     *
     * @return bool
     * @throws DBALException
     */
    public function isDownloadModifier(int $productId, int $modifierIdentifierId): bool
    {
        $query = 'SELECT `products_attributes_filename` FROM `products_attributes_download` WHERE `products_attributes_id` IN (
                      SELECT `products_attributes_id` FROM `products_attributes` WHERE `options_values_id` = :products_options_values_id  AND `products_id` = :products_id
                  )';
        
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue('products_options_values_id' , $modifierIdentifierId);
        $stmt->bindValue('products_id' , $productId);
        $stmt->execute();
        
        $result = $stmt->fetchAll();
        
        return count($result) !== 0;
    }
}