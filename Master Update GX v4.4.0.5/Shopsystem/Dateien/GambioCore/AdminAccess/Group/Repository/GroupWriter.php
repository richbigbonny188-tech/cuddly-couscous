<?php
/* --------------------------------------------------------------
   GroupWriter.php 2020-08-03
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Group\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Gambio\Core\AdminAccess\Group\Exceptions\DeletionOfGroupsFailed;
use Gambio\Core\AdminAccess\Group\Exceptions\ParentGroupIdDoesNotExist;
use Gambio\Core\AdminAccess\Group\Exceptions\StorageOfGroupsFailed;
use Gambio\Core\AdminAccess\Group\Group;
use Gambio\Core\AdminAccess\Group\GroupDescriptions;
use Gambio\Core\AdminAccess\Group\GroupId;
use Gambio\Core\AdminAccess\Group\GroupIds;
use Gambio\Core\AdminAccess\Group\GroupNames;
use Gambio\Core\Language\LanguageService;

/**
 * Class GroupWriter
 *
 * @package Gambio\Core\AdminAccess\Group\Repositories
 */
class GroupWriter
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
     * GroupWriter constructor.
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
     * @param GroupNames        $names
     * @param GroupDescriptions $descriptions
     * @param int               $sortOrder
     * @param bool              $isProtected
     * @param int|null          $parentGroupId
     *
     * @return int
     */
    public function createGroup(
        GroupNames $names,
        GroupDescriptions $descriptions,
        int $sortOrder,
        bool $isProtected = false,
        ?int $parentGroupId = null
    ): int {
        $this->db->createQueryBuilder()
            ->insert('admin_access_groups')
            ->setValue('parent_id', ':parentId')
            ->setValue('sort_order', ':sortOrder')
            ->setValue('protected', ':isProtected')
            ->setParameter('parentId', $parentGroupId ?? 0)
            ->setParameter('sortOrder', $sortOrder)
            ->setParameter('isProtected', $isProtected ? 1 : 0)
            ->execute();
        
        $groupId = (int)$this->db->lastInsertId();
        foreach ($this->languageService->getAvailableLanguages() as $language) {
            $this->db->createQueryBuilder()
                ->insert('admin_access_group_descriptions')
                ->setValue('admin_access_group_id', ':groupId')
                ->setValue('language_id', ':languageId')
                ->setValue('name', ':name')
                ->setValue('description', ':description')
                ->setParameter('groupId', $groupId)
                ->setParameter('languageId', $language->id())
                ->setParameter('name', $names->getName($language->id()))
                ->setParameter('description', $descriptions->getDescription($language->id()))
                ->execute();
        }
        
        return $groupId;
    }
    
    
    /**
     * @param Group ...$groups
     *
     * @return int[]
     *
     * @throws StorageOfGroupsFailed
     */
    public function storeGroups(Group ...$groups): array
    {
        $ids = [];
        $this->db->beginTransaction();
        
        try {
            foreach ($groups as $group) {
                $this->updateGroup($group);
                $ids[] = $group->id();
            }
            
            $this->db->commit();
        } catch (DBALException $exception) {
            $this->db->rollBack();
            
            throw StorageOfGroupsFailed::becauseOfException($exception);
        }
        
        return $ids;
    }
    
    
    /**
     * @param Group $group
     */
    private function updateGroup(Group $group): void
    {
        try {
            $parentGroupId = $group->parentGroupId();
        } catch (ParentGroupIdDoesNotExist $e) {
            $parentGroupId = 0;
        }
        
        $this->db->createQueryBuilder()
            ->update('admin_access_groups')
            ->set('parent_id', ':parentId')
            ->set('sort_order', ':sortOrder')
            ->set('protected', ':isProtected')
            ->where('admin_access_group_id = :groupId')
            ->setParameter('parentId', $parentGroupId)
            ->setParameter('sortOrder', $group->sortOrder())
            ->setParameter('isProtected', $group->isProtected() ? 1 : 0)
            ->setParameter('groupId', $group->id())
            ->execute();
        
        $this->addGroupItems($group->id(), $group);
        $this->addGroupDetails($group->id(), $group);
    }
    
    
    /**
     * @param int   $groupId
     * @param Group $group
     */
    private function addGroupItems(int $groupId, Group $group): void
    {
        $this->db->createQueryBuilder()
            ->delete('admin_access_group_items')
            ->where('admin_access_group_id = :groupId')
            ->setParameter('groupId', $groupId)
            ->execute();
        
        foreach ($group->groupItems() as $groupItem) {
            $this->db->createQueryBuilder()
                ->insert('admin_access_group_items')
                ->setValue('admin_access_group_id', ':groupId')
                ->setValue('identifier', ':descriptor')
                ->setValue('type', ':type')
                ->setParameter('groupId', $groupId)
                ->setParameter('descriptor', $groupItem->descriptor())
                ->setParameter('type', $groupItem->type())
                ->execute();
        }
    }
    
    
    /**
     * @param int   $groupId
     * @param Group $group
     */
    private function addGroupDetails(int $groupId, Group $group): void
    {
        $this->db->createQueryBuilder()
            ->delete('admin_access_group_descriptions')
            ->where('admin_access_group_id = :groupId')
            ->setParameter('groupId', $groupId)
            ->execute();
        
        foreach ($this->languageService->getAvailableLanguages() as $language) {
            $this->db->createQueryBuilder()
                ->insert('admin_access_group_descriptions')
                ->setValue('admin_access_group_id', ':groupId')
                ->setValue('language_id', ':languageId')
                ->setValue('name', ':name')
                ->setValue('description', ':description')
                ->setParameter('groupId', $groupId)
                ->setParameter('languageId', $language->id())
                ->setParameter('name', $group->name($language->id()))
                ->setParameter('description', $group->description($language->id()))
                ->execute();
        }
    }
    
    
    /**
     * @param GroupIds $groupIds
     *
     * @throws ConnectionException
     *
     * @throws DeletionOfGroupsFailed
     */
    public function deleteGroups(GroupIds $groupIds): void
    {
        $this->db->beginTransaction();
        try {
            foreach ($groupIds as $groupId) {
                $this->deleteGroup($groupId);
            }
            
            $this->db->commit();
        } catch (DBALException $exception) {
            $this->db->rollBack();
            
            throw DeletionOfGroupsFailed::becauseOfException($exception);
        }
    }
    
    
    /**
     * @param GroupId $id
     */
    private function deleteGroup(GroupId $id): void
    {
        $this->db->createQueryBuilder()
            ->delete('admin_access_groups')
            ->where('admin_access_group_id = :groupId')
            ->setParameter('groupId', $id->value())
            ->execute();
        
        $this->db->createQueryBuilder()
            ->delete('admin_access_group_items')
            ->where('admin_access_group_id = :groupId')
            ->setParameter('groupId', $id->value())
            ->execute();
        
        $this->db->createQueryBuilder()
            ->delete('admin_access_group_descriptions')
            ->where('admin_access_group_id = :groupId')
            ->setParameter('groupId', $id->value())
            ->execute();
    }
}