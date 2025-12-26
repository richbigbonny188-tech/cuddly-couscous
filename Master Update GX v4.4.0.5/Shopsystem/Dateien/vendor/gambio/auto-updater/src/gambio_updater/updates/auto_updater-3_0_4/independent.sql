/* Delete old gx_configurations values */
DELETE FROM `gx_configurations` WHERE `key` = "gm_configuration/HUB_CONNECTOR_UPDATER_URL";
DELETE FROM `gx_configurations` WHERE `key` = "gm_configuration/UPDATE_DOWNLOADER_URL";
DELETE FROM `gx_configurations` WHERE `key` = "gm_configuration/UPDATE_DOWNLOADER_CHECK_URL";
DELETE FROM `gx_configurations` WHERE `key` = "gm_configuration/UPDATE_DOWNLOADER_NOTIFY_URL";
