<?php
/* --------------------------------------------------------------
 Definition.php 2020-09-11
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
 * Interface Definition
 * @package Gambio\Core\Contracts\Application\DependencyInjection
 */
interface Definition
{
    /**
     * Adds a new constructor argument to a type definition.
     *
     * @param mixed $arg
     *
     * @return $this
     */
    public function addArgument($arg): self;
    
    
    /**
     * Adds constructor arguments to a type definition.
     *
     * @param array $args
     *
     * @return $this
     */
    public function addArguments(array $args): self;
}