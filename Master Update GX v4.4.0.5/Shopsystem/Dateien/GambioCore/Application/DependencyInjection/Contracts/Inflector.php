<?php
/* --------------------------------------------------------------
 Inflector.php 2020-09-11
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
 * Interface Inflector
 * @package Gambio\Core\Contracts\Application\DependencyInjection
 */
interface Inflector
{
    /**
     * Invokes the defined method if an inflected type is resolved by the container.
     *
     * @param string $name
     * @param array  $args
     */
    public function invokeMethod(string $name, array $args): void;
}