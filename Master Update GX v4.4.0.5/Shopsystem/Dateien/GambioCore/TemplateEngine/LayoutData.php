<?php
/* --------------------------------------------------------------
 Data.php 2020-09-03
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
 * Interface Data
 * @package Gambio\Core\TemplateEngine
 */
interface LayoutData
{
    /**
     * Assigns the key to the given value.
     *
     * @param string $key
     * @param        $value
     */
    public function assign(string $key, $value): void;
    
    
    /**
     * Returns the assigned value of $key or null, if nothing was found.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key);
    
    
    /**
     * Returns an array with all assigned key value pairs.
     *
     * @return array
     */
    public function toArray(): array;
}