<?php
/* --------------------------------------------------------------
   GroupReader.php 2020-05-29
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
use Gambio\Core\AdminAccess\Group\Exceptions\GroupDoesNotExist;
use Gambio\Core\AdminAccess\Group\GroupId;
use Gambio\Core\AdminAccess\Group\GroupItem;

/**
 * Class GroupReader
 *
 * @package Gambio\Core\AdminAccess\Group\Repositories
 */
class GroupReader
{
    /**
     * @var Connection
     */
    private $db;
    
    
    /**
     * GroupReader constructor.
     *
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }
    
    
    /**
     * @param GroupId $id
     *
     * @return array<string, string|array|bool|int>
     *
     * @throws GroupDoesNotExist
     */
    public function getGroupDataById(GroupId $id): array
    {
        $groupData = $this->db->createQueryBuilder()
            ->select('admin_access_group_id, parent_id, sort_order, protected')
            ->from('admin_access_groups')
            ->where('admin_access_group_id = :id')
            ->orderBy('sort_order')
            ->setParameter('id', $id->value())
            ->execute()
            ->fetch();
        
        if ($groupData === false) {
            throw GroupDoesNotExist::forId($id->value());
        }
        
        $groupDetails = $this->getGroupDetails($id->value());
        $groupItems   = $this->getGroupItems($id->value());
        
        return [
            'id'            => (int)$groupData['admin_access_group_id'],
            'parentGroupId' => ((int)$groupData['parent_id'] > 0) ? (int)$groupData['parent_id'] : null,
            'names'         => $groupDetails['names'],
            'descriptions'  => $groupDetails['descriptions'],
            'items'         => $groupItems,
            'sortOrder'     => (int)$groupData['sort_order'],
            'isProtected'   => $groupData['protected'] === '1',
        ];
    }
    
    
    /**
     * @param GroupItem $groupItem
     *
     * @return array<string, string|array|bool|int>
     *
     * @throws GroupDoesNotExist
     */
    public function getGroupDataByItem(GroupItem $groupItem): array
    {
        $groupData = $this->db->createQueryBuilder()
            ->select('aag.admin_access_group_id, aag.parent_id, aag.sort_order, aag.protected')
            ->from('admin_access_groups', 'aag')
            ->join('aag', 'admin_access_group_items', 'aagi', 'aag.admin_access_group_id = aagi.admin_access_group_id')
            ->where('aagi.identifier = :descriptor')
            ->andWhere('aagi.type = :type')
            ->orderBy('aag.sort_order')
            ->setParameter('descriptor', $groupItem->descriptor())
            ->setParameter('type', $groupItem->type())
            ->execute()
            ->fetch();
        
        if ($groupData === false) {
            throw GroupDoesNotExist::forDescriptorAndType($groupItem->descriptor(), $groupItem->type());
        }
        
        $groupDetails = $this->getGroupDetails((int)$groupData['admin_access_group_id']);
        $groupItems   = $this->getGroupItems((int)$groupData['admin_access_group_id']);
        
        return [
            'id'            => (int)$groupData['admin_access_group_id'],
            'parentGroupId' => ((int)$groupData['parent_id'] > 0) ? (int)$groupData['parent_id'] : null,
            'names'         => $groupDetails['names'],
            'descriptions'  => $groupDetails['descriptions'],
            'items'         => $groupItems,
            'sortOrder'     => (int)$groupData['sort_order'],
            'isProtected'   => $groupData['protected'] === '1',
        ];
    }
    
    
    /**
     * @return array<array<string, string|array|bool|int>>
     */
    public function getGroupsData(): array
    {
        $groups     = [];
        $groupsData = $this->db->createQueryBuilder()
            ->select('admin_access_group_id, parent_id, sort_order, protected')
            ->from('admin_access_groups')
            ->orderBy('sort_order')
            ->execute()
            ->fetchAll();
        
        foreach ($groupsData as $groupData) {
            $groupDetails = $this->getGroupDetails((int)$groupData['admin_access_group_id']);
            $groupItems   = $this->getGroupItems((int)$groupData['admin_access_group_id']);
            
            $groups[] = [
                'id'            => (int)$groupData['admin_access_group_id'],
                'parentGroupId' => ((int)$groupData['parent_id'] > 0) ? (int)$groupData['parent_id'] : null,
                'names'         => $groupDetails['names'],
                'descriptions'  => $groupDetails['descriptions'],
                'items'         => $groupItems,
                'sortOrder'     => (int)$groupData['sort_order'],
                'isProtected'   => $groupData['protected'] === '1',
            ];
        }
        
        return $groups;
    }
    
    
    /**
     * @param int $id
     *
     * @return array<string, array<int, string>>
     */
    private function getGroupDetails(int $id): array
    {
        $groupDetails = $this->db->createQueryBuilder()
            ->select('language_id, name, description')
            ->from('admin_access_group_descriptions')
            ->where('admin_access_group_id = :groupId')
            ->setParameter('groupId', $id)
            ->execute()
            ->fetchAll();
        
        $names        = [];
        $descriptions = [];
        foreach ($groupDetails as $groupDetail) {
            $names[(int)$groupDetail['language_id']]        = $groupDetail['name'];
            $descriptions[(int)$groupDetail['language_id']] = $groupDetail['description'];
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
    private function getGroupItems(int $id): array
    {
        return $this->db->createQueryBuilder()
            ->select('type, identifier as descriptor')
            ->from('admin_access_group_items')
            ->where('admin_access_group_id = :groupId')
            ->setParameter('groupId', $id)
            ->execute()
            ->fetchAll();
    }
}