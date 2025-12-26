<?php
/*--------------------------------------------------------------
   new_tax_zone.php 2021-02-17
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

declare(strict_types=1);

/** @var DatabaseModel $this */
/** @var bool $t_success */
$countryData = [
    'name' => 'Northern Ireland',
    'iso-2' => 'IX',
    'iso-3' => 'NIR'
];

// Adds Northern Ireland to the countries table if it does not exists
$query = "SELECT `countries_id` FROM `countries` WHERE `countries_iso_code_3` = 'NIR';";
$countriesResult = $this->query($query, true);

if ($countriesResult->num_rows === 0) {
    $insert = "INSERT INTO `countries` (`countries_name`, `countries_iso_code_2`, `countries_iso_code_3`, `address_format_id`, `status`, `is_state_mandatory`) VALUES ('{$countryData['name']}', '{$countryData['iso-2']}', '{$countryData['iso-3']}', 1, 0, 0);";
    $nirCountry = $this->query($insert);
    $t_success &= ($nirCountry !== false);
} else {
    $nirCountry = $countriesResult->fetch_assoc()['countries_id'];
}

// Adds Northern Ireland to the tax zone
$query = "SELECT `geo_zone_id` FROM `geo_zones` WHERE `geo_zone_name` = '{$countryData['name']}';";
$zonesResult = $this->query($query, true);

if ($zonesResult->num_rows === 0) {
    $insert = "INSERT INTO `geo_zones` (`geo_zone_name`, `geo_zone_description`, `last_modified`, `date_added`) VALUES('Northern Ireland', '', CURRENT_TIMESTAMP, now())";
    $geoZoneId = $this->query($insert);
    $t_success &= ($geoZoneId !== false);
} else {
    $geoZoneId = $zonesResult->fetch_assoc()['geo_zone_id'];
}

// Moves Northern Ireland into the NIR tax zone
$query = "SELECT `association_id` FROM zones_to_geo_zones WHERE zone_country_id = {$nirCountry} AND geo_zone_id = {$geoZoneId};";
$geoZonesResult = $this->query($query, true);

if ($geoZonesResult->num_rows === 0) {
    $insert = "INSERT INTO `zones_to_geo_zones` (`zone_country_id`, `zone_id`, `geo_zone_id`, `last_modified`, `date_added`) VALUES ({$nirCountry}, 0, $geoZoneId, CURRENT_TIMESTAMP, now());";
    $t_success &= $this->query($insert, true);
}

// Moves Northern Ireland into the NIR EU tax zone
$euTaxZone = 5;
$query = "SELECT `association_id` FROM zones_to_geo_zones WHERE zone_country_id = {$nirCountry} AND geo_zone_id = {$euTaxZone};";
$geoZonesResult = $this->query($query, true);

if ($geoZonesResult->num_rows === 0) {
    $insert = "INSERT INTO `zones_to_geo_zones` (`zone_country_id`, `zone_id`, `geo_zone_id`, `last_modified`, `date_added`) VALUES ({$nirCountry}, 0, $euTaxZone, CURRENT_TIMESTAMP, now());";
    $t_success &= $this->query($insert, true);
}
