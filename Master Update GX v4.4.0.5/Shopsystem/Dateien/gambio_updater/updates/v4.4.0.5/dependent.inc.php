<?php
/*--------------------------------------------------------------
   dependent.inc.php 2022-02-24
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2022 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

include_once __DIR__ . '/google_services_repair.php';

/** @var bool $t_success */

if ($this->table_exists('gx_configurations')) {
    
    $queryIsCookieConsentInstalled =
        $this->query('SELECT `value` FROM `gx_configurations` WHERE `key` = "modules/GambioCookieConsentPanel/label_button_advanced_settings"');
    $isCookieConsentInstalled = isset($queryIsCookieConsentInstalled[0]);
    
    $check =
        $this->query('SELECT `value` FROM `gx_configurations` WHERE `key` = "modules/GambioCookieConsentPanel/label_button_only_essentials"',
                     true);
    
    if ($isCookieConsentInstalled && $check->num_rows < 1) {
        $query =
            'INSERT INTO `gx_configurations` (`key`, `value`) VALUES (\'modules/GambioCookieConsentPanel/label_button_only_essentials\', \'{\"de\":\"Nur Notwendige\",\"en\":\"Only Essentials\"}\'), (\'modules/GambioCookieConsentPanel/only_essentials_button_status\', \'0\')';
        $t_success &= $this->query($query, true);
    }
} else {
    
    $queryIsCookieConsentInstalled =
        $this->query('SELECT `value` FROM `configuration_storage` WHERE `key` = "modules/GambioCookieConsentPanel/label_button_advanced_settings"');
    $isCookieConsentInstalled = isset($queryIsCookieConsentInstalled[0]);
    
    $check =
        $this->query('SELECT `value` FROM `configuration_storage` WHERE `key` = "modules/GambioCookieConsentPanel/label_button_only_essentials"',
                     true);
    
    if ($isCookieConsentInstalled && $check->num_rows < 1) {
        $query =
            'INSERT INTO `configuration_storage` (`key`, `value`) VALUES (\'modules/GambioCookieConsentPanel/label_button_only_essentials\', \'{\"de\":\"Nur Notwendige\",\"en\":\"Only Essentials\"}\'), (\'modules/GambioCookieConsentPanel/only_essentials_button_status\', \'0\')';
        $t_success &= $this->query($query, true);
    }
}
