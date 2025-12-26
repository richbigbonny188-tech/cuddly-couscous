<?php
/*--------------------------------------------------------------
   dependent.inc.php 2021-04-09
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

declare(strict_types=1);

$googleUnits = ['kg', 'g', 'mg', 'ml', 'cl', 'l', 'cm', 'm', 'sqm', 'cbm','ct'];

function getVpeIdByName($name, $db)
{
    $vpeCheck = $db->query("SELECT `products_vpe_id` FROM `products_vpe` WHERE `products_vpe_name` = '" . $name . "'");
    if (!empty($vpeCheck)) {
        return $vpeCheck[0]['products_vpe_id'];
    }

    $vpeIdCheck = $db->query('SELECT MAX(`products_vpe_id`)+1 AS "id" FROM `products_vpe`');
    if (!empty($vpeIdCheck[0]['id'])) {
        return $vpeIdCheck[0]['id'];
    }

    return 1;
}

function getQuantityUnitIdByName($name, $db, &$success)
{
    $quantityUnitCheck = $db->query("SELECT `quantity_unit_id` FROM `quantity_unit_description` WHERE `unit_name`='"
                                    . $name . "'");
    if (!empty($quantityUnitCheck)) {
        return $quantityUnitCheck[0]['quantity_unit_id'];
    }

    $quantityUnitIDCheck = $db->query('SELECT MAX(`quantity_unit_id`)+1 AS id FROM `quantity_unit`');
    if (!empty($quantityUnitIDCheck[0]['id'])) {
        $quantityUnitID = $quantityUnitIDCheck[0]['id'];

        $query   = "INSERT INTO `quantity_unit` VALUES ($quantityUnitID)";
        $success &= $db->query($query, true);

        return $quantityUnitID;
    }

    $query   = "INSERT INTO `quantity_unit` VALUES (1)";
    $success &= $db->query($query, true);

    return 1;
}

/**
 * Delete invalid data
 */
$query     = "DELETE a FROM `quantity_unit` a LEFT JOIN `quantity_unit_description` b USING (`quantity_unit_id`) WHERE b.`quantity_unit_id` IS NULL";
$t_success &= is_numeric($this->query($query));

$query     = "DELETE a FROM `quantity_unit_description` a LEFT JOIN `quantity_unit` b USING (`quantity_unit_id`) WHERE b.`quantity_unit_id` IS NULL";
$t_success &= is_numeric($this->query($query));

$query     = "DELETE a FROM `products_quantity_unit` a LEFT JOIN `quantity_unit` b USING (`quantity_unit_id`) WHERE b.`quantity_unit_id` IS NULL";
$t_success &= is_numeric($this->query($query));

/**
 * Store new units
 */
foreach ($googleUnits as $unit) {
    $vpeId          = getVpeIdByName($unit, $this);
    $quantityUnitId = getQuantityUnitIdByName($unit, $this, $t_success);

    foreach ($this->getLanguages() as $language) {
        $vpeCheck = $this->query("SELECT * FROM `products_vpe` WHERE `products_vpe_id` = $vpeId AND `language_id` = "
                                 . $language['languages_id'],
                                 true);

        if ($vpeCheck->num_rows < 1) {
            $query     = "INSERT INTO products_vpe SET `products_vpe_id` = " . $vpeId . ", language_id = "
                         . $language['languages_id'] . ", products_vpe_name = '" . $unit . "'";
            $t_success &= $this->query($query, true);
        }

        $quantityUnitCheck = $this->query("SELECT * FROM `quantity_unit_description` WHERE `quantity_unit_id` = $quantityUnitId AND `language_id` = "
                                          . $language['languages_id'],
                                          true);

        if ($quantityUnitCheck->num_rows < 1) {
            $query     = "INSERT INTO `quantity_unit_description` VALUES  ($quantityUnitId, "
                         . $language['languages_id'] . ", '" . $unit . "')";
            $t_success &= $this->query($query, true);
        }
    }
}

