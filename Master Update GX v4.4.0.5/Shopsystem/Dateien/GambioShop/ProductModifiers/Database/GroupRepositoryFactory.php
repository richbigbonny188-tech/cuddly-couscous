<?php
/*--------------------------------------------------------------------------------------------------
    GroupRepositoryFactory.php 2020-02-17
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */
declare(strict_types=1);

namespace Gambio\Shop\ProductModifiers\Database;

use Gambio\Shop\ProductModifiers\Database\Core\Factories\Interfaces\GroupRepositoryFactoryInterface;
use Gambio\Shop\ProductModifiers\Database\Core\GroupRepository;
use Gambio\Shop\ProductModifiers\Groups\Repositories\GroupRepositoryInterface;
use LegacyDependencyContainer;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class GroupRepositoryFactory
 *
 * @package Gambio\Shop\ProductModifiers\Database
 */
class GroupRepositoryFactory implements GroupRepositoryFactoryInterface
{
    /**
     * @var EventDispatcherInterface|null
     */
    private $dispatcher;

    /**
     * GroupRepositoryFactory constructor.
     *
     * @param EventDispatcherInterface|null $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher = null)
    {
        $this->dispatcher = $dispatcher ?? LegacyDependencyContainer::getInstance()->get(EventDispatcherInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function createRepository(): GroupRepositoryInterface
    {
        return new GroupRepository($this->dispatcher);
    }
}