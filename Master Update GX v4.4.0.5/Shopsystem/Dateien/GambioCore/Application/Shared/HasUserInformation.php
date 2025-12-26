<?php
/* --------------------------------------------------------------
 HasUserInformation.php 2020-09-15
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Shared;

/**
 * Interface HasUserInformation
 * @package Gambio\Core\Contracts\Shared
 */
interface HasUserInformation
{
    /**
     * The customer id is maybe not set.
     * If authenticated returns true, customerId must return a value.
     *
     * @return int|null
     */
    public function userId(): ?int;
    
    
    /**
     * Checks if current user is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated(): bool;
}