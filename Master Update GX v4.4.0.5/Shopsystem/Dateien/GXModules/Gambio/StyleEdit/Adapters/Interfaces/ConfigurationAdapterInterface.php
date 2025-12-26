<?php
/*--------------------------------------------------------------------------------------------------
    ConfigurationAdapterInterface.php 2020-08-13
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace GXModules\Gambio\StyleEdit\Adapters\Interfaces;

interface ConfigurationAdapterInterface
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key);
    
    
    /**
     * @param string $key
     * @param        $value
     *
     * @return void
     */
    public function set(string $key, $value): void;
}