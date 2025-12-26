<?php
/*--------------------------------------------------------------
   dependent.inc.php 2021-04-26
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

declare(strict_types=1);

/** @var bool $t_success */
/** @var mysqli_result $check */
$labelIntoConfig = $this->query('SELECT * FROM `gx_configurations` WHERE `key` = "modules/GambioCookieConsentPanel/label_intro";', true);
if ($labelIntoConfig->num_rows === 1) {
    
    $faultyIntroHash = 'f5eb26647b4365e42e9a9c7f893dbac303bf7e7f89498565f8a031fbbbb5b09ac8dbad52ed5d8f76515b5a71db495391f86f98405ead6e01e74bd62ff96bfb7b';
    $labelIntroJson  = $labelIntoConfig->fetch_assoc()['value'];
    $labelIntroValue = json_decode($labelIntroJson, false);
    $labelIntroHash  = hash('sha512', $labelIntroValue->de);
    
    if ($faultyIntroHash === $labelIntroHash) {
    
        $t_success &= $this->query('UPDATE `gx_configurations` SET `value` = REPLACE(`value`, "  ", " ") WHERE `key` = "modules/GambioCookieConsentPanel/label_intro";', true);
    }
}

if (true === $this->table_exists('gkv_shipments')) {
    $t_success &= $this->query(
        'ALTER TABLE `gkv_shipments` CHANGE `labelurl` `labelurl` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `returnlabelurl` `returnlabelurl` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `exportlabelurl` `exportlabelurl` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `codlabelurl` `codlabelurl` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
',
        true
    );
}


/**
 * Update footer copyright year
 */

$rows = $this->query("SELECT `id`, `value` FROM `gx_configurations` WHERE `key` = 'gm_configuration/GM_FOOTER'");
if (!empty($rows)) {
    foreach ($rows as $row) {
        $oldValue     = $row['value'];
        $newValue     = preg_replace('/20[\d]{2}/', '2021', $oldValue);
        $escapedValue = $this->coo_mysqli->real_escape_string($newValue);
        $escapedId    = $this->coo_mysqli->real_escape_string($row['id']);
        
        $t_query   = "UPDATE `gx_configurations`  SET `value` = '{$escapedValue}' WHERE `id` = {$escapedId};";
        $t_success &= is_numeric($this->query($t_query));
    }
}

include __DIR__ . '/remove_absolute_urls_from_settings.json.php';

$check = $this->query("SELECT * FROM `gx_configurations` WHERE `key` = 'gm_configuration/ATTACH_WITHDRAWAL_INFO_IN_ORDER_CONFIRMATION';", true);
if ($check->num_rows < 1) {
    $query     = "INSERT INTO gx_configurations  (`key`, `language_id`, `value`, `default`, `type`, `sort_order`,`legacy_group_id`) VALUES ('gm_configuration/ATTACH_WITHDRAWAL_INFO_IN_ORDER_CONFIRMATION', NULL, '1', '1', '1-0-switcher', NULL, NULL);";
    $t_success &= $this->query($query, true);
}
