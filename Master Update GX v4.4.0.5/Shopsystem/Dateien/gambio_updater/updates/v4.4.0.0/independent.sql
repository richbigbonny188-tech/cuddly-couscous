UPDATE `gx_configurations` SET `value`=REPLACE(`value`,'target="_blank"','target="_blank" rel="noopener"') WHERE `key` LIKE '%GM_FOOTER%' AND `value` NOT LIKE '%rel="noopener"%';

-- MOVE UK FROM Steuerzone EU TO Steuerzone EU-Ausland
UPDATE zones_to_geo_zones SET geo_zone_id = 6 WHERE association_id = 222 AND zone_country_id = 222 AND geo_zone_id = 5;
