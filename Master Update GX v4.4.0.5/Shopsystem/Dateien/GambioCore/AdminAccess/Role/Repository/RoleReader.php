<?php
/* --------------------------------------------------------------
   RoleReader.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Role\Repository;

use Doctrine\DBAL\Connection;
use Gambio\Core\AdminAccess\Role\AdminId;
use Gambio\Core\AdminAccess\Role\Exceptions\RoleDoesNotExist;
use Gambio\Core\AdminAccess\Role\RoleId;

/**
 * Class RoleReader
 *
 * @package Gambio\Core\AdminAccess\Role\Repositories
 */
class RoleReader
{
    /**
     * @var Connection
     */
    private $db;
    
    
    /**
     * RoleReader constructor.
     *
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }
    
    
    /**
     * @param RoleId $id
     *
     * @return array<string, string|array|bool|int>
     *
     * @throws RoleDoesNotExist
     */
    public function getRoleDataById(RoleId $id): array
    {
        $roleData = $this->db->createQueryBuilder()
            ->select('admin_access_role_id, sort_order, protected')
            ->from('admin_access_roles')
            ->where('admin_access_role_id = :id')
            ->setParameter('id', $id->value())
            ->execute()
            ->fetch();
        
        if ($roleData === false) {
            throw RoleDoesNotExist::forId($id->value());
        }
        
        $roleDetails     = $this->getRoleDetails($id->value());
        $rolePermissions = $this->getRolePermissions($id->value());
        
        return [
            'id'           => (int)$roleData['admin_access_role_id'],
            'names'        => $roleDetails['names'],
            'descriptions' => $roleDetails['descriptions'],
            'permissions'  => $rolePermissions,
            'sortOrder'    => (int)$roleData['sort_order'],
            'isProtected'  => $roleData['protected'] === '1',
        ];
    }
    
    
    /**
     * @return array<array<string, string|array|bool|int>>
     */
    public function getRolesData(): array
    {
        $roles     = [];
        $rolesData = $this->db->createQueryBuilder()
            ->select('admin_access_role_id, sort_order, protected')
            ->from('admin_access_roles')
            ->execute()
            ->fetchAll();
        
        foreach ($rolesData as $roleData) {
            $roleDetails     = $this->getRoleDetails((int)$roleData['admin_access_role_id']);
            $rolePermissions = $this->getRolePermissions((int)$roleData['admin_access_role_id']);
            
            $roles[] = [
                'id'           => (int)$roleData['admin_access_role_id'],
                'names'        => $roleDetails['names'],
                'descriptions' => $roleDetails['descriptions'],
                'permissions'  => $rolePermissions,
                'sortOrder'    => (int)$roleData['sort_order'],
                'isProtected'  => $roleData['protected'] === '1',
            ];
        }
        
        return $roles;
    }
    
    
    /**
     * @param AdminId $admin
     *
     * @return array<array<string, string|array|bool|int>>
     */
    public function getRolesDataByAdmin(AdminId $admin): array
    {
        $roles     = [];
        $rolesData = $this->db->createQueryBuilder()
            ->select('aar.admin_access_role_id, aar.sort_order, aar.protected')
            ->from('admin_access_roles', 'aar')
            ->join('aar', 'admin_access_users', 'aau', 'aar.admin_access_role_id = aau.admin_access_role_id')
            ->where('aau.customer_id = :adminId')
            ->setParameter('adminId', $admin->value())
            ->execute()
            ->fetchAll();
        
        foreach ($rolesData as $roleData) {
            $roleDetails     = $this->getRoleDetails((int)$roleData['admin_access_role_id']);
            $rolePermissions = $this->getRolePermissions((int)$roleData['admin_access_role_id']);
            
            $roles[] = [
                'id'           => (int)$roleData['admin_access_role_id'],
                'names'        => $roleDetails['names'],
                'descriptions' => $roleDetails['descriptions'],
                'permissions'  => $rolePermissions,
                'sortOrder'    => (int)$roleData['sort_order'],
                'isProtected'  => $roleData['protected'] === '1',
            ];
        }
        
        return $roles;
    }
    
    
    /**
     * @param int $id
     *
     * @return array<string, array<int, string>>
     */
    private function getRoleDetails(int $id): array
    {
        $roleDetails = $this->db->createQueryBuilder()
            ->select('language_id, name, description')
            ->from('admin_access_role_descriptions')
            ->where('admin_access_role_id = :roleId')
            ->setParameter('roleId', $id)
            ->execute()
            ->fetchAll();
        
        $names        = [];
        $descriptions = [];
        foreach ($roleDetails as $roleDetail) {
            $names[$roleDetail['language_id']]        = $roleDetail['name'];
            $descriptions[$roleDetail['language_id']] = $roleDetail['description'];
        }
        
        return [
            'names'        => $names,
            'descriptions' => $descriptions,
        ];
    }
    
    
    /**
     * @param int $id
     *
     * @return array
     */
    private function getRolePermissions(int $id): array
    {
        $permissions = $this->db->createQueryBuilder()
            ->select('admin_access_group_id, reading_granted, writing_granted, deleting_granted')
            ->from('admin_access_permissions')
            ->where('admin_access_role_id = :roleId')
            ->setParameter('roleId', $id)
            ->execute()
            ->fetchAll();
        
        return array_map(static function (array $permission): array {
            return [
                'groupId'         => (int)$permission['admin_access_group_id'],
                'readingGranted'  => $permission['reading_granted'] === '1',
                'writingGranted'  => $permission['writing_granted'] === '1',
                'deletingGranted' => $permission['deleting_granted'] === '1',
            ];
        },
            $permissions);
    }
}