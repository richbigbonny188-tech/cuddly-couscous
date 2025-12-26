<?php
/* --------------------------------------------------------------
 Loader.php 2020-09-03
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\TemplateEngine;

/**
 * Interface Loader
 * @package Gambio\Core\TemplateEngine
 */
interface Loader
{
    /**
     * Loads template data.
     *
     * @param LayoutData $data
     */
    public function load(LayoutData $data): void;
}
