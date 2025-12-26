<?php
/*--------------------------------------------------------------
   dependent.inc.php 2021-04-07
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

declare(strict_types=1);

include __DIR__ . '/new_tax_zone.php';

/** @var DatabaseModel $this */
/** @var bool $t_success */

if ($this->columnExists('google_export_availability', 'language_id') === false) {
    
    $queries = [
        'DROP TABLE IF EXISTS `google_export_availability`;',
        "CREATE TABLE `google_export_availability` (
            `google_export_availability_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `language_id` int(10) NOT NULL DEFAULT 2,
            `availability` varchar(64) NOT NULL DEFAULT '',
            PRIMARY KEY (`google_export_availability_id`, `language_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=`utf8` ;",
        "INSERT INTO `google_export_availability` (`google_export_availability_id`, `language_id`, `availability`) VALUES
        (1, 2, 'auf lager'),
        (3, 2, 'nicht auf lager'),
        (4, 2, 'vorbestellt'),
        (1, 1, 'in stock'),
        (3, 1, 'out of stock'),
        (4, 1, 'preorder');"
    ];
    
    foreach ($queries as $query) {
        
        $t_success &= $this->query($query, true);
    }
}
