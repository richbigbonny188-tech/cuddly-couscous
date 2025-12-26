<?php
/* --------------------------------------------------------------
   RoleWriter.php 2020-08-03
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
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Gambio\Core\AdminAccess\Role\Exceptions\DeletionOfRolesFailed;
use Gambio\Core\AdminAccess\Role\Exceptions\StorageOfRolesFailed;
use Gambio\Core\AdminAccess\Role\Role;
use Gambio\Core\AdminAccess\Role\RoleDescriptions;
use Gambio\Core\AdminAccess\Role\RoleId;
use Gambio\Core\AdminAccess\Role\RoleIds;
use Gambio\Core\AdminAccess\Role\RoleNames;
use Gambio\Core\Language\LanguageService;

/**
 * Class RoleWriter
 *
 * @package Gambio\Core\AdminAccess\Role\Repositories
 */
class RoleWriter
{
    /**
     * @var Connection
     */
    private $db;
    
    /**
     * @var LanguageService
     */
    private $languageService;
    
    
    /**
     * RoleWriter constructor.
     *
     * @param Connection      $db
     * @param LanguageService $languageService
     */
    public function __construct(Connection $db, LanguageService $languageService)
    {
        $this->db              = $db;
        $this->languageService = $languageService;
    }
    
    
    /**
     * @param Role ...$roles
     *
     * @return int[]
     *
     * @throws StorageOfRolesFailed
     */
    public function storeRoles(Role ...$roles): array
    {
        $ids = [];
        $this->db->beginTransaction();
        
        try {
            foreach ($roles as $role) {
                $this->updateRole($role);
                $ids[] = $role->id();
            }
            
            $this->db->commit();
        } catch (DBALException $exception) {
            $this->db->rollBack();
            
            throw StorageOfRolesFailed::becauseOfException($exception);
        }
        
        return $ids;
    }
    
    
    /**
     * @param RoleNames        $names
     * @param RoleDescriptions $descriptions
     * @param int              $sortOrder
     * @param bool             $isProtected
     *
     * @return int
     */
    public function createRole(RoleNames $names, RoleDescriptions $descriptions, int $sortOrder, bool $isProtected): int
    {
        $this->db->createQueryBuilder()
            ->insert('admin_access_roles')
            ->setValue('sort_order', ':sortOrder')
            ->setValue('protected', ':isProtected')
            ->setParameter('sortOrder', $sortOrder)
            ->setParameter('isProtected', $isProtected ? '1' : '0')
            ->execute();
        
        $roleId = (int)$this->db->lastInsertId();
        
        foreach ($this->languageService->getAvailableLanguages() as $language) {
            $this->db->createQueryBuilder()
                ->insert('admin_access_role_descriptions')
                ->setValue('admin_access_role_id', ':roleId')
                ->setValue('language_id', ':languageId')
                ->setValue('name', ':name')
                ->setValue('description', ':description')
                ->setParameter('roleId', $roleId)
                ->setParameter('languageId', $language->id())
                ->setParameter('name', $names->getName($language->id()))
                ->setParameter('description', $descriptions->getDescription($language->id()))
                ->execute();
        }
        
        return $roleId;
    }
    
    
    /**
     * @param Role $role
     */
    private function updateRole(Role $role): void
    {
        $this->db->createQueryBuilder()
            ->update('admin_access_roles')
            ->set('sort_order', ':sortOrder')
            ->set('protected', ':isProtected')
            ->where('admin_access_role_id', ':roleId')
            ->setParameter('sortOrder', $role->sortOrder())
            ->setParameter('isProtected', $role->isProtected() ? '1' : '0')
            ->setParameter('roleId', $role->id())
            ->execute();
        
        $this->addRolePermissions($role->id(), $role);
        $this->addRoleDetails($role->id(), $role);
    }
    
    
    /**
     * @param int  $roleId
     * @param Role $role
     */
    private function addRolePermissions(int $roleId, Role $role): void
    {
        $this->db->createQueryBuilder()
            ->delete('admin_access_permissions')
            ->where('admin_access_role_id = :roleId')
            ->setParameter('roleId', $roleId)
            ->execute();
        
        foreach ($role->permissions() as $permission) {
            $this->db->createQueryBuilder()
                ->insert('admin_access_permissions')
                ->setValue('admin_access_role_id', ':roleId')
                ->setValue('admin_access_group_id', ':groupId')
                ->setValue('reading_granted', ':readingGranted')
                ->setValue('writing_granted', ':writingGranted')
                ->setValue('deleting_granted', ':deletingGranted')
                ->setParameter('roleId', $roleId)
                ->setParameter('groupId', $permission->groupId())
                ->setParameter('readingGranted', $permission->readingGranted() ? '1' : '0')
                ->setParameter('writingGranted', $permission->writingGranted() ? '1' : '0')
                ->setParameter('deletingGranted', $permission->deletingGranted() ? '1' : '0')
                ->execute();
        }
    }
    
    
    /**
     * @param int  $roleId
     * @param Role $role
     */
    private function addRoleDetails(int $roleId, Role $role): void
    {
        $this->db->createQueryBuilder()
            ->delete('admin_access_role_descriptions')
            ->where('admin_access_role_id = :roleId')
            ->setParameter('roleId', $roleId)
            ->execute();
        
        foreach ($this->languageService->getAvailableLanguages() as $language) {
            $this->db->createQueryBuilder()
                ->insert('admin_access_role_descriptions')
                ->setValue('admin_access_role_id', ':roleId')
                ->setValue('language_id', ':languageId')
                ->setValue('name', ':name')
                ->setValue('description', ':description')
                ->setParameter('roleId', $roleId)
                ->setParameter('languageId', $language->id())
                ->setParameter('name', $role->name($language->id()))
                ->setParameter('description', $role->description($language->id()))
                ->execute();
        }
    }
    
    
    /**
     * @param RoleIds $roleIds
     *
     * @throws ConnectionException
     *
     * @throws DeletionOfRolesFailed
     */
    public function deleteRoles(RoleIds $roleIds): void
    {
        $this->db->beginTransaction();
        try {
            foreach ($roleIds as $roleId) {
                $this->deleteRole($roleId);
            }
            
            $this->db->commit();
        } catch (DBALException $exception) {
            $this->db->rollBack();
            
            throw DeletionOfRolesFailed::becauseOfException($exception);
        }
    }
    
    
    /**
     * @param RoleId $id
     */
    private function deleteRole(RoleId $id): void
    {
        $this->db->createQueryBuilder()
            ->delete('admin_access_roles')
            ->where('admin_access_role_id = :roleId')
            ->setParameter('roleId', $id->value())
            ->execute();
        
        $this->db->createQueryBuilder()
            ->delete('admin_access_users')
            ->where('admin_access_role_id = :roleId')
            ->setParameter('roleId', $id->value())
            ->execute();
        
        $this->db->createQueryBuilder()
            ->delete('admin_access_permissions')
            ->where('admin_access_role_id = :roleId')
            ->setParameter('roleId', $id->value())
            ->execute();
        
        $this->db->createQueryBuilder()
            ->delete('admin_access_role_descriptions')
            ->where('admin_access_role_id = :roleId')
            ->setParameter('roleId', $id->value())
            ->execute();
    }
}