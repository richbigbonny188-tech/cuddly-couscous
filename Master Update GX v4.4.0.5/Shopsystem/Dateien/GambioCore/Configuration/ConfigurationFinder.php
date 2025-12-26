<?php
/* --------------------------------------------------------------
 ConfigurationFinder.php 2020-04-16
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Configuration;

/**
 * Interface ConfigurationFinder
 * @package Gambio\Core\Configuration
 */
interface ConfigurationFinder
{
    /**
     * Searches for a configuration.
     *
     * This method tries to find the configuration entry for the given key.
     * If a configuration was found, the related value will be returned as string.
     * In case of nothing was found, or the configuration equals null, null is returned.
     *
     * @param string      $key
     * @param string|null $default
     *
     * @return string|null
     */
    public function get(string $key, string $default = null): ?string;
}