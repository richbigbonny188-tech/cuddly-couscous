<?php
/* --------------------------------------------------------------
 dependent.inc.php 2020-11-26
 Gambio GmbH
 http://www.gambio.de

 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

/** @var DatabaseModel $this */

/** @var mysqli_result $check */
$check = $this->query("SELECT * FROM `gx_configurations` WHERE `key` like '%SHOW_SUBCATEGORIES_PARENT%';", true);
if ($check->num_rows === 0) {
    $query     = "INSERT INTO `gx_configurations` (`key`, `value`, `default`, `last_modified`) VALUES ('gm_configuration/SHOW_SUBCATEGORIES_PARENT', 'false', 'false', NOW());";
    $t_success &= $this->query($query, true);
}

/** @var mysqli_result $check */
$check = $this->query("SHOW INDEX FROM `products_properties_combis` WHERE Key_name = 'products_properties_combis_cheapest'", true);
if ($check->num_rows < 1) {
    $query     = "CREATE INDEX `products_properties_combis_cheapest` ON `products_properties_combis`(`products_id` asc, `combi_price` asc);";
    $t_success &= $this->query($query, true);
}
