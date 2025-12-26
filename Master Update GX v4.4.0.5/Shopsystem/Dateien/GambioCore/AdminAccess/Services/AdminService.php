<?php
/* --------------------------------------------------------------
   AdminService.php 2020-08-03
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Services;

use Gambio\Core\AdminAccess\Admin\Admin;
use Gambio\Core\AdminAccess\Admin\AdminFactory;
use Gambio\Core\AdminAccess\Admin\AdminIds;
use Gambio\Core\AdminAccess\Admin\Admins;
use Gambio\Core\AdminAccess\Admin\Repository\AdminRepository;

/**
 * Class AdminService
 *
 * @package Gambio\Core\AdminAccess\Admin\Services
 */
class AdminService implements \Gambio\Core\AdminAccess\AdminService
{
    /**
     * @var AdminRepository
     */
    private $repository;
    
    /**
     * @var AdminFactory
     */
    private $factory;
    
    
    /**
     * AdminService constructor.
     *
     * @param AdminRepository $repository
     * @param AdminFactory    $factory
     */
    public function __construct(AdminRepository $repository, AdminFactory $factory)
    {
        $this->repository = $repository;
        $this->factory    = $factory;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getAdmins(): Admins
    {
        return $this->repository->getAdmins();
    }
    
    
    /**
     * @inheritDoc
     */
    public function getAdminById(int $id): Admin
    {
        return $this->repository->getAdminById($this->factory->createAdminId($id));
    }
    
    
    /**
     * @inheritDoc
     */
    public function storeAdmins(Admin ...$admins): AdminIds
    {
        return $this->repository->storeAdmins(...$admins);
    }
}