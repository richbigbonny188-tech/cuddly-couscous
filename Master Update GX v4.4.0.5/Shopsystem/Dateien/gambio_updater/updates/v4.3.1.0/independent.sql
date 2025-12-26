
CREATE TABLE IF NOT EXISTS `redirectrules` (
	`redirect_id` int(11) NOT NULL AUTO_INCREMENT,
	`url_path` varchar(200) NOT NULL,
	`query` varchar(200) NOT NULL,
	`query_match_mode` varchar(12) NOT NULL DEFAULT 'ignore',
	`response_code` int(11) NOT NULL DEFAULT '302',
	`target` varchar(200) NOT NULL,
	`query_processing` varchar(6) NOT NULL DEFAULT 'merge',
	`status` int(1) NOT NULL DEFAULT '1',
	PRIMARY KEY (`redirect_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `brickfox_export`;
DROP TABLE IF EXISTS `brickfox_orders`;
DROP TABLE IF EXISTS `brickfox_orders_lines`;

DELETE FROM `gx_configurations` WHERE `legacy_group_id` = "19";

UPDATE `gx_configurations` SET `value` = 'true' WHERE `key` = 'gm_configuration/GM_QUICK_SEARCH';

INSERT INTO `gx_configurations` (`key`,`language_id`,`value`,`default`,`type`,`sort_order`,`legacy_group_id`,`last_modified`) SELECT * FROM (SELECT 'configuration/dashboard/EMBED_SOCIAL_MEDIA' as `key`,null as language_id,'false' as value,'false' as default_value,'switcher' as type, null as sort_order,10 as legacy_group_id,NOW() as last_modified) AS tmp WHERE NOT EXISTS (SELECT id FROM gx_configurations WHERE `key` = 'configuration/dashboard/EMBED_SOCIAL_MEDIA') LIMIT 1;

DELETE FROM `admin_access_group_items` WHERE `identifier` = 'gm_slider.php';
DROP TABLE IF EXISTS `category_slider_set`;
DROP TABLE IF EXISTS `content_slider_set`;
DROP TABLE IF EXISTS `products_slider_set`;
DROP TABLE IF EXISTS `slider_image`;
DROP TABLE IF EXISTS `slider_image_area`;
DROP TABLE IF EXISTS `slider_image_description`;

DELETE FROM `admin_access_group_items` WHERE `identifier` = 'gm_gmotion.php';
DELETE FROM `gx_configurations` WHERE `key` LIKE '%GM_GMOTION_STANDARD%';

DELETE FROM `admin_access_group_items` WHERE `identifier` = 'accounting.php';

DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_THUMBNAIL_BEVEL';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_THUMBNAIL_GREYSCALE';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_THUMBNAIL_ELLIPSE';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_THUMBNAIL_ROUND_EDGES';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_THUMBNAIL_MERGE';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_THUMBNAIL_FRAME';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_THUMBNAIL_DROP_SHADDOW';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_THUMBNAIL_MOTION_BLUR';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_INFO_BEVEL';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_INFO_GREYSCALE';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_INFO_ELLIPSE';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_INFO_ROUND_EDGES';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_INFO_MERGE';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_INFO_FRAME';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_INFO_DROP_SHADDOW';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_INFO_MOTION_BLUR';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_POPUP_BEVEL';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_POPUP_GREYSCALE';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_POPUP_ELLIPSE';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_POPUP_ROUND_EDGES';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_POPUP_MERGE';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_POPUP_FRAME';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_POPUP_DROP_SHADDOW';
DELETE FROM `gx_configurations` WHERE `key` = 'configuration/PRODUCT_IMAGE_POPUP_MOTION_BLUR';

ALTER TABLE `product_image_list_image` CHANGE `product_image_list_image_local_path` `product_image_list_image_local_path` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

UPDATE `gx_configurations` SET `value` = 'false' WHERE `gx_configurations`.`key` = 'gm_configuration/GM_COOKIE_STATUS';
