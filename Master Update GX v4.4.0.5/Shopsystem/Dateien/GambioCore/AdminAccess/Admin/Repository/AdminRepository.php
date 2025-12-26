<?php
/* --------------------------------------------------------------
   AdminRepository.php 2020-08-03
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Admin\Repository;

use Gambio\Core\AdminAccess\Admin\Admin;
use Gambio\Core\AdminAccess\Admin\AdminId;
use Gambio\Core\AdminAccess\Admin\AdminIds;
use Gambio\Core\AdminAccess\Admin\Admins;
use Gambio\Core\AdminAccess\Admin\Exceptions\AdminDoesNotExist;
use Gambio\Core\AdminAccess\Admin\Exceptions\StorageOfAdminsFailed;
use Gambio\Core\Event\Abstracts\AbstractEventDispatchingRepository;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class AdminRepository
 *
 * @package Gambio\Core\AdminAccess\Admin\Repositories
 */
class AdminRepository extends AbstractEventDispatchingRepository
{
    /**
     * @var AdminMapper
     */
    private $mapper;
    
    /**
     * @var AdminReader
     */
    private $reader;
    
    /**
     * @var AdminWriter
     */
    private $writer;
    
    
    /**
     * AdminRepository constructor.
     *
     * @param AdminMapper              $mapper
     * @param AdminReader              $reader
     * @param AdminWriter              $writer
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        AdminMapper $mapper,
        AdminReader $reader,
        AdminWriter $writer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->mapper = $mapper;
        $this->reader = $reader;
        $this->writer = $writer;
        
        $this->setEventDispatcher($eventDispatcher);
    }
    
    
    /**
     * @return Admins
     */
    public function getAdmins(): Admins
    {
        return $this->mapper->mapAdmins($this->reader->getAdminsData());
    }
    
    
    /**
     * @param AdminId $adminId
     *
     * @return Admin
     *
     * @throws AdminDoesNotExist
     */
    public function getAdminById(AdminId $adminId): Admin
    {
        return $this->mapper->mapAdmin($this->reader->getAdminDataById($adminId));
    }
    
    
    /**
     * @param Admin ...$admins
     *
     * @return AdminIds
     *
     * @throws StorageOfAdminsFailed
     */
    public function storeAdmins(Admin ...$admins): AdminIds
    {
        $ids = $this->writer->storeAdmins(...$admins);
        foreach ($admins as $admin) {
            $this->dispatchEntityEvents($admin);
        }
        
        return $this->mapper->mapAdminIds($ids);
    }
}