<?php

/* --------------------------------------------------------------
   OrderStatusReader.inc.php 2017-03-30
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2017 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class OrderStatusReader
 *
 * @category   System
 * @package    OrderStatus
 * @subpackage Repositories
 */
class OrderStatusReader implements OrderStatusReaderInterface
{
    /**
     * @var CI_DB_query_builder
     */
    protected $queryBuilder;
    
    /**
     * @var string
     */
    protected $table = 'orders_status';
    
    /**
     * @var string
     */
    protected $id = 'orders_status_id';
    
    
    /**
     * OrderStatusReader constructor.
     *
     * @param \CI_DB_query_builder $queryBuilder Active record instance for data access.
     */
    public function __construct(CI_DB_query_builder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }
    
    
    /**
     * Returns the data of the expected order status entity by the given order status id.
     *
     * @param \IntType $orderStatusId Id of expected order status entity
     *
     * @return array Data of order status entity or empty array, if no data was found.
     */
    public function getOrderStatusById(IntType $orderStatusId)
    {
        $resultData = $this->queryBuilder->select()
            ->from($this->table)
            ->where($this->id, $orderStatusId->asInt())
            ->order_by($this->id, 'asc')
            ->order_by('language_id', 'asc')
            ->get()
            ->result_array();
        
        $names = [];
        if (count($resultData) === 0) {
            return $resultData;
        }
        foreach ($resultData as $data) {
            $names[(int)$data['language_id']] = $data['orders_status_name'];
        }
        
        return [
            'id'    => $resultData[0][$this->id],
            'names' => $names,
            'color' => $resultData[0]['color']
        ];
    }
    
    
    /**
     * Returns the data of all order status resources in the storage.
     *
     * @return array Data of all order status entities.
     */
    public function getAllOrderStatuses()
    {
        $resultData  = $this->queryBuilder->select()
            ->from($this->table)
            ->order_by($this->id, 'asc')
            ->order_by('language_id',
                       'asc')
            ->get()
            ->result_array();
        
        $allOrderStatuses = [];
        foreach ($resultData as $result) {
            if (!array_key_exists((int)$result[$this->id], $allOrderStatuses)) {
                $allOrderStatuses[(int)$result[$this->id]] = [
                    'id'    => (int)$result[$this->id],
                    'names' => [
                        (int)$result['language_id'] => $result['orders_status_name'],
                    ],
                    'color' => $result['color']
                ];
            } else {
                $allOrderStatuses[(int)$result[$this->id]]['names'][(int)$result['language_id']] = $result['orders_status_name'];
            }
        }
        
        return $allOrderStatuses;
    }
    
    
    /**
     * Alias of getAllOrderStatuses()
     *
     * @return array
     */
    public function getAllOrderStatus()
    {
        return $this->getAllOrderStatuses();
    }
}