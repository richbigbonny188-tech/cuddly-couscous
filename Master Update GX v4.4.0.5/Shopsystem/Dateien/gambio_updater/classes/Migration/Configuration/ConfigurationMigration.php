<?php
/* --------------------------------------------------------------
 ConfigurationMigration.php 2020-01-16
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 16 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Configuration\Migration;

/**
 * Interface ConfigurationMigration
 * @package Gambio\Core\Configuration\Migration
 */
interface ConfigurationMigration
{
    public function migrate();
}