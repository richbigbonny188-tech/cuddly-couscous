<?php
/* --------------------------------------------------------------
   Permission.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Role\Model;

use Gambio\Core\AdminAccess\Role\GroupId;

/**
 * Class Permission
 *
 * @package Gambio\Core\AdminAccess\Role\Models
 */
class Permission implements \Gambio\Core\AdminAccess\Role\Permission
{
    /**
     * @var GroupId
     */
    private $groupId;
    
    /**
     * @var bool
     */
    private $readingGranted;
    
    /**
     * @var bool
     */
    private $writingGranted;
    
    /**
     * @var bool
     */
    private $deletingGranted;
    
    
    /**
     * Permission constructor.
     *
     * @param GroupId $groupId
     * @param bool    $readingGranted
     * @param bool    $writingGranted
     * @param bool    $deletingGranted
     */
    private function __construct(GroupId $groupId, bool $readingGranted, bool $writingGranted, bool $deletingGranted)
    {
        $this->groupId         = $groupId;
        $this->readingGranted  = $readingGranted;
        $this->writingGranted  = $writingGranted;
        $this->deletingGranted = $deletingGranted;
    }
    
    
    /**
     * @param GroupId $groupId
     * @param bool    $readingGranted
     * @param bool    $writingGranted
     * @param bool    $deletingGranted
     *
     * @return Permission
     */
    public static function create(
        GroupId $groupId,
        bool $readingGranted,
        bool $writingGranted,
        bool $deletingGranted
    ): Permission {
        return new self($groupId, $readingGranted, $writingGranted, $deletingGranted);
    }
    
    
    /**
     * @inheritDoc
     */
    public function groupId(): int
    {
        return $this->groupId->value();
    }
    
    
    /**
     * @inheritDoc
     */
    public function readingGranted(): bool
    {
        return $this->readingGranted;
    }
    
    
    /**
     * @inheritDoc
     */
    public function writingGranted(): bool
    {
        return $this->writingGranted;
    }
    
    
    /**
     * @inheritDoc
     */
    public function deletingGranted(): bool
    {
        return $this->deletingGranted;
    }
}