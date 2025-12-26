<?php
/* --------------------------------------------------------------
   AdminReader.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Admin\Repository;

use Doctrine\DBAL\Connection;
use Gambio\Core\AdminAccess\Admin\AdminId;
use Gambio\Core\AdminAccess\Admin\Exceptions\AdminDoesNotExist;

/**
 * Class AdminReader
 *
 * @package Gambio\Core\AdminAccess\Admin\Repositories
 */
class AdminReader
{
    /**
     * @var Connection
     */
    private $db;
    
    
    /**
     * AdminReader constructor.
     *
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }
    
    
    /**
     * @param AdminId $id
     *
     * @return array
     *
     * @throws AdminDoesNotExist
     */
    public function getAdminDataById(AdminId $id): array
    {
        $adminData = $this->db->createQueryBuilder()
            ->select('c.customers_id, c.customers_firstname, c.customers_lastname')
            ->addSelect('GROUP_CONCAT(aau.admin_access_role_id SEPARATOR \',\') as assigned_roles')
            ->from('customers', 'c')
            ->join('c', 'admin_access_users', 'aau', 'c.customers_id = aau.customer_id')
            ->groupBy('c.customers_id, c.customers_firstname, c.customers_lastname')
            ->where('c.customers_id = :adminId')
            ->setParameter('adminId', $id->value())
            ->execute()
            ->fetch();
        
        if ($adminData === false) {
            throw AdminDoesNotExist::forId($id->value());
        }
        
        return [
            'id'            => (int)$adminData['customers_id'],
            'firstName'     => $adminData['customers_firstname'],
            'lastName'      => $adminData['customers_lastname'],
            'assignedRoles' => array_map('intval', explode(',', $adminData['assigned_roles'])),
        ];
    }
    
    
    /**
     * @return array
     */
    public function getAdminsData(): array
    {
        $adminsData = $this->db->createQueryBuilder()
            ->select('c.customers_id, c.customers_firstname, c.customers_lastname')
            ->addSelect('GROUP_CONCAT(aau.admin_access_role_id SEPARATOR \',\') as assigned_roles')
            ->from('customers', 'c')
            ->join('c', 'admin_access_users', 'aau', 'c.customers_id = aau.customer_id')
            ->groupBy('c.customers_id, c.customers_firstname, c.customers_lastname')
            ->orderBy('c.customers_id')
            ->execute()
            ->fetchAll();
        
        return array_map(static function (array $adminData): array {
            return [
                'id'            => (int)$adminData['customers_id'],
                'firstName'     => $adminData['customers_firstname'],
                'lastName'      => $adminData['customers_lastname'],
                'assignedRoles' => array_map('intval', explode(',', $adminData['assigned_roles'])),
            ];
        },
            $adminsData);
    }
}