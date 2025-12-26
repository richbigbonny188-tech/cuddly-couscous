<?php
/* --------------------------------------------------------------
   dependent.inc.php 2018-05-23
   Gambio GmbH
   http://www.gambio.de
   Copyright © 2017 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/* @var \DatabaseModel $this */

// Add or update AUTO_UPDATER_UPDATES_URL gx_configurations value
$check = $this->query('SELECT * FROM `gx_configurations` WHERE `key` = "gm_configuration/AUTO_UPDATER_UPDATES_URL";', true);
if($check->num_rows < 1)
{
	$t_success &= $this->query('
		INSERT INTO `gx_configurations`
		SET `key`    = "gm_configuration/AUTO_UPDATER_UPDATES_URL",
			`value`  = "https://updates.gambio-support.de/v2/check.php";
	') !== false;
}
else
{
	$t_success &= $this->query('
		UPDATE  `gx_configurations`
		SET     `value`  = "https://updates.gambio-support.de/v2/check.php"
		WHERE   `key`    = "gm_configuration/AUTO_UPDATER_UPDATES_URL";
	') !== false;
}

// Add or update AUTO_UPDATER_FEEDBACK_URL gx_configurations value
$check = $this->query('SELECT * FROM `gx_configurations` WHERE `key` = "gm_configuration/AUTO_UPDATER_FEEDBACK_URL";', true);
if($check->num_rows < 1)
{
	$t_success &= $this->query('
		INSERT INTO `gx_configurations`
		SET `key`    = "gm_configuration/AUTO_UPDATER_FEEDBACK_URL",
			`value`  = "https://updates.gambio-support.de/v2/callingHome.php";
	') !== false;
}
else
{
	$t_success &= $this->query('
		UPDATE  `gx_configurations`
		SET     `value`  = "https://updates.gambio-support.de/v2/callingHome.php"
		WHERE   `key`    = "gm_configuration/AUTO_UPDATER_FEEDBACK_URL";
	') !== false;
}

// Add or update AUTO_UPDATER_FEEDBACK_URL gx_configurations value
$check  = $this->query('SELECT * FROM `gx_configurations` WHERE `key` = "gm_configuration/UPDATE_DOWNLOADER_ACCEPT_DATA_PRIVACY";',
                       true);
$check2 = $this->query('SELECT * FROM `gx_configurations` WHERE `key` = "gm_configuration/AUTO_UPDATER_ACCEPT_DATA_PROCESSING";',
                       true);
if($check->num_rows > 0 && $check->num_rows < 1)
{
	$t_success &= $this->query('UPDATE `gx_configurations` SET `key` = "gm_configuration/AUTO_UPDATER_ACCEPT_DATA_PROCESSING" WHERE `key` = "gm_configuration/UPDATE_DOWNLOADER_ACCEPT_DATA_PRIVACY";')
	              !== false;
}

// Add new controller to admin access
if($this->table_exists('admin_access_group_descriptions') && $this->table_exists('admin_access_group_items')
   && $this->table_exists('admin_access_groups'))
{
	/* @var \mysqli_result $groupsQuery */
	$groupsQuery = $this->query('SELECT `admin_access_group_id` FROM `admin_access_group_descriptions` WHERE `name` = "AutoUpdater" GROUP BY `admin_access_group_id`;',
	                            true);
	
	$newControllers = [
		'AutoUpdaterAjax',
		'AutoUpdater',
		'AutoUpdaterShopExcludedAjax',
	];
	
	while($groupsQuery->num_rows > 0 && $group = $groupsQuery->fetch_array())
	{
		foreach($newControllers as $newController)
		{
			$t_success &= $this->query(sprintf('
				REPLACE INTO `admin_access_group_items` (`admin_access_group_id`, `identifier`, `type`)
				VALUES (%d, "%s", "%s");
			', (int)$group['admin_access_group_id'], $newController, 'CONTROLLER')) !== false;
		}
	}
}
