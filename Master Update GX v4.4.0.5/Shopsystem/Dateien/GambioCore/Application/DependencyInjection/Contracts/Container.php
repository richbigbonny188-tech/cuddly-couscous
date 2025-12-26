<?php
/* --------------------------------------------------------------
 Container.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\DependencyInjection\Contracts;

use Psr\Container\ContainerInterface;

/**
 * Interface Container
 * @package Gambio\Core\Contracts\Application
 */
interface Container extends ContainerInterface, Registry
{
    /**
     * Registers a service provider.
     *
     * @param ToLeagueServiceProvider $provider
     *
     * @return $this
     */
    public function registerProvider(ToLeagueServiceProvider $provider): self;
}
