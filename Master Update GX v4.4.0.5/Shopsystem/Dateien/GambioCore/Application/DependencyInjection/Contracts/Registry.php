<?php
/* --------------------------------------------------------------
 Registry.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\DependencyInjection\Contracts;

/**
 * Interface Registry
 * @package Gambio\Core\Contracts\Application\DependencyInjection
 */
interface Registry
{
    /**
     * Registers a definition to the application.
     *
     * @param string     $id
     * @param mixed|null $concrete
     *
     * @return Definition
     */
    public function register(string $id, $concrete = null): Definition;
    
    
    /**
     * Registers a shared definition to the application.
     *
     * @param string     $id
     * @param mixed|null $concrete
     *
     * @return Definition
     */
    public function registerShared(string $id, $concrete = null): Definition;
    
    
    /**
     * Inflects a type.
     *
     * If an inflected type is requested from the container, the inflector performs some task
     * (invokes methods on the type) before resolve and return it.
     *
     * @param string        $type
     * @param callable|null $callback
     *
     * @return Inflector
     */
    public function inflect(string $type, callable $callback = null): Inflector;
}