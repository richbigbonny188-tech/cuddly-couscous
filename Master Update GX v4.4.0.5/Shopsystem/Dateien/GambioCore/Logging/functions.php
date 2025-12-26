<?php
/* --------------------------------------------------------------
 functions.php 2020-09-07
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Logging;

use Psr\Log\LoggerInterface;

if (!function_exists('logger')) {
    /**
     * Creates a new logger.
     *
     * @param string $namespace
     * @param bool   $addRequestData
     *
     * @return LoggerInterface
     */
    function logger(string $namespace = 'general', bool $addRequestData = false): LoggerInterface
    {
        return LoggerFactory::create($namespace, $addRequestData);
    }
}
