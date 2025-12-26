<?php
/* --------------------------------------------------------------
   AdminWriter.php 2020-08-03
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
use Doctrine\DBAL\DBALException;
use Gambio\Core\AdminAccess\Admin\Admin;
use Gambio\Core\AdminAccess\Admin\Exceptions\StorageOfAdminsFailed;

/**
 * Class AdminWriter
 *
 * @package Gambio\Core\AdminAccess\Admin\Repositories
 */
class AdminWriter
{
    /**
     * @var Connection
     */
    private $db;
    
    
    /**
     * AdminWriter constructor.
     *
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }
    
    
    /**
     * @param Admin ...$admins
     *
     * @return int[]
     *
     * @throws StorageOfAdminsFailed
     */
    public function storeAdmins(Admin ...$admins): array
    {
        $ids = [];
        $this->db->beginTransaction();
        
        try {
            foreach ($admins as $admin) {
                $this->updateAdmin($admin);
                $ids[] = $admin->id();
            }
            
            $this->db->commit();
        } catch (DBALException $exception) {
            $this->db->rollBack();
            
            throw StorageOfAdminsFailed::becauseOfException($exception);
        }
        
        return $ids;
    }
    
    
    /**
     * @param Admin $admin
     */
    private function updateAdmin(Admin $admin): void
    {
        $this->db->createQueryBuilder()
            ->delete('admin_access_users')
            ->where('customer_id = :id')
            ->setParameter('id', $admin->id())
            ->execute();
        
        foreach ($admin->assignedRoleIds() as $assignedRoleId) {
            $this->db->createQueryBuilder()
                ->insert('admin_access_users')
                ->setValue('customer_id', ':adminId')
                ->setValue('admin_access_role_id', ':roleId')
                ->setParameter('adminId', $admin->id())
                ->setParameter('roleId', $assignedRoleId->value())
                ->execute();
        }
    }
}