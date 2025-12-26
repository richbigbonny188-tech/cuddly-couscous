DELETE FROM `admin_access_permissions` WHERE `admin_access_group_id` NOT IN (SELECT `admin_access_group_id` FROM `admin_access_groups`);
REPLACE INTO `admin_access_permissions` (`admin_access_role_id`, `admin_access_group_id`, `reading_granted`, `writing_granted`, `deleting_granted`) (SELECT 1, `admin_access_group_id`, 1, 1, 1 FROM `admin_access_groups`);

UPDATE `gx_configurations` SET `default` = 'R_{INVOICE_ID}_2021' WHERE `key` = 'gm_configuration/GM_INVOICE_ID';
UPDATE `gx_configurations` SET `default` = 'L_{DELIVERY_ID}_2021' WHERE `key` = 'gm_configuration/GM_PACKINGS_ID';
DELETE FROM `gx_configurations` WHERE `key` LIKE 'configuration/AFTERBUY_%';

UPDATE gx_configurations SET `value` = IF(`value` = '1' OR `value` = 'true', 'true', 'false') WHERE `key` = 'gm_configuration\/GM_SHOW_WISHLIST';

 -- cleaning up content_manager_aliases table
DELETE FROM `content_manager_aliases` WHERE `content_group` NOT IN (SELECT `content_group` from `content_manager`);
UPDATE gx_configurations SET `value` = IF(`value` = '1' OR `value` = 'true', 'true', 'false') WHERE `key` = 'gm_configuration\/ALWAYS_SHOW_CONTINUE_SHOPPING_BUTTON';

UPDATE gx_configurations SET `value` = IF(`value` = '1' OR `value` = 'true', '1', '0'), `type` = '1-0-switcher' WHERE `key` = 'gm_configuration\/GM_TITLE_USE_STANDARD_META_TITLE';
