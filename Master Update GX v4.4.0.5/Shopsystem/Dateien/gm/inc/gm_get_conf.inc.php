<?php
/*--------------------------------------------------------------------
 gm_get_conf.inc.php 2021-04-15
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

/**
 * Fetches configuration values by the given key.
 * Returns null if no configuration item was found by provided key or its value is null.
 *
 * @param string|array $key        Key of configuration to be fetched.
 * @param string       $type       Either "ASSOC" or "NUMERIC". Will be ignored, if $key is not an array.
 * @param bool         $renewCache Renew cache if return value has changed during this request.
 *
 * @return array|string|null Array if $key is an array, string otherwise and null if nothing was found.
 *
 * @deprecated This method shouldn't be used for new domains and exist for compatibility purposes.
 */
function gm_get_conf($key, $type = 'ASSOC', $renewCache = false)
{
    static $configurationValues;
    if ($configurationValues === null) {
        $configurationValues = [];
    }

    // build configuration values cache
    if ($renewCache === true || count($configurationValues) === 0) {
        $prefix = 'gm_configuration/';
        $result = xtc_db_query("SELECT `key`, `value` FROM `gx_configurations` WHERE `key` LIKE '${prefix}%'",
                               'db_link',
                               false);
        while ($row = xtc_db_fetch_array($result)) {
            $configurationKey                       = strtoupper(str_replace($prefix, '', $row['key']));
            $configurationValues[$configurationKey] = $row['value'];
        }
    }

    if (is_array($key)) { // return array
        $configurationValue = [];

        foreach ($key as $configurationKey) {
            $configurationValuesKey = strtoupper($configurationKey);
            if ($type === 'ASSOC') {
                $configurationValue[$configurationKey] = $configurationValues[$configurationValuesKey] ?? null;
            } else {
                $configurationValue[] = $configurationValues[$configurationValuesKey] ?? null;
            }
        }
    } else { // return string or null
        $configurationKey   = strtoupper($key);
        $configurationValue = $configurationValues[$configurationKey] ?? null;
    }

    return $configurationValue;
}
