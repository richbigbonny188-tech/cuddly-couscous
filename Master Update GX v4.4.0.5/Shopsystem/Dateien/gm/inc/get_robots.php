<?php
/*--------------------------------------------------------------------
 get_robots.php 2020-10-28
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

require_once(DIR_FS_CATALOG . 'gm/inc/gm_get_language.inc.php');

/*
 * creates a robot file and exports it
 *
 * @param string $p_shop_path Shop path
 * @return string
 */
function get_robots($p_shop_path, $p_content_links = '', $p_save = false)
{
	
	// SETTNGS
	$use_seo_boost_language_code = gm_get_conf('USE_SEO_BOOST_LANGUAGE_CODE');
	
	// LANGUAGES
	$languages = [];
	foreach(gm_get_language() as $language)
	{
		$languages[$language['languages_id']] = $language;
	}


	// USE GIVEN LINKS FROM PARAM
	// Or create from static seo urls, content manager and sitemaps
	$t_content_links = '';
	if($p_content_links != '')
	{
		$t_content_links = (string)$p_content_links;
	}
	else
	{

		// STATIC SEO URLS
		// Static files which should not be indexed
		$query = xtc_db_query('
			SELECT `name`
			FROM `static_seo_urls`
			WHERE `robots_disallow_entry` = 1
		');
		while($page = xtc_db_fetch_array($query))
		{
			if($use_seo_boost_language_code === 'true')
			{
				foreach($languages as $language)
				{
					$lines[] = sprintf('Disallow: {PATH}%s/%s', $language['code'], $page['name']);
				}
			}
			else
			{
				$lines[] = sprintf('Disallow: {PATH}%s', $page['name']);
			}
		}

		// CONTENT MANAGER
		// Collect non indexable pages
		$query = xtc_db_query('
			SELECT 
				`content_manager`.`content_id`,
				`content_manager`.`content_group`,
				`content_manager`.`gm_url_keywords`,
				`content_manager`.`languages_id` 
			FROM `content_manager`,languages
			WHERE `gm_robots_entry` = "1"
            AND content_manager.languages_id = languages.languages_id AND languages.status=1
		');
		while ($content = xtc_db_fetch_array($query)) 
		{
			if ($use_seo_boost_language_code === 'true')
			{
				$lines[] = sprintf('Disallow: {PATH}%s/info/%s.html', $languages[(int)$content['languages_id']]['code'], $content['gm_url_keywords']);
			}
			else
			{
				$lines[] = sprintf('Disallow: {PATH}info/%s.html', $content['gm_url_keywords']);
			}

			// Static Paths with content group ids
			$lines[] = sprintf('Disallow: {PATH}shop_content.php?coID=%s', $content['content_group']);
			$lines[] = sprintf('Disallow: {PATH}popup_content.php?coID=%s', $content['content_group']);
		}

		// SITEMAPS
        
        $file = 'public/sitemap_index.xml';
        if(file_exists(DIR_FS_CATALOG . $file))
        {
            $lines[] = sprintf('Sitemap: %s%s%s',
                (ENABLE_SSL_CATALOG === 'true' ? HTTPS_CATALOG_SERVER : HTTP_CATALOG_SERVER), DIR_WS_CATALOG,
                $file);
        }

		$t_content_links = implode(PHP_EOL, $lines);
	}


	// CREATE FILE
    $t_file = DIR_FS_CATALOG.'export/robots.txt.tpl';
	$t_lines = file($t_file);
    $t_result = '';
    foreach($t_lines as $line) 
    {
        $t_result .= str_replace('{PATH}', $p_shop_path, $line);
    }
	$t_result .= PHP_EOL . str_replace('{PATH}', $p_shop_path, $t_content_links);

    // check SSL
    if(ENABLE_SSL_CATALOG === 'true' || ENABLE_SSL_CATALOG === true) {
        // check if ssl is in a subdirectory
        $t_http_parsed = parse_url(HTTPS_CATALOG_SERVER);
        if(isset($t_http_parsed['path'])) {
            $t_result .= "\n\n";
            $t_path = substr($t_http_parsed['path'], 1);
            if(substr($t_path, -1, 1) != '/') {
                $t_path = $t_path.'/';
            }
            // again for ssl
            foreach($t_lines as $line) {
                $t_result .= str_replace('{PATH}', $p_shop_path.$t_path, $line);
            }
			$t_result .= str_replace('{PATH}', $p_shop_path.$t_path, $t_content_links);
        }
    }

	// convert into UNIX-file format
	$t_result = str_replace("\r\n", "\n", $t_result);
	// convert into Windows-file format
	$t_result = str_replace("\n", "\r\n", $t_result);

	// SAVE OR DOWNLOAD
	if ($p_save === true)
	{
		// Save robots.txt file to document root 
		$documentRoot = get_robots_path();
		$success = @file_put_contents($documentRoot, $t_result);
		
		return $success;
	}
	else
	{
		// Download robots.txt file
		header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
		header("Last-Modified: " . gmdate("D,d M Y H:i:s")." GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-Type: application/octet-stream");
		header("Content-disposition: attachment; filename=\"robots.txt\"");
		header('Content-Length: ' . strlen($t_result));
		echo $t_result;
		
		exit;
	}
}


/**
 * Get directory to save robots.txt
 *
 * @return string
 */
function get_robots_path()
{
	return substr(DIR_FS_CATALOG, 0, (strlen(DIR_WS_CATALOG) * -1)) . '/robots.txt';
}


/**
 * check if robots.txt obsolete
 *
 * @param string $p_shop_path Shop path
 * @return bool
 */
function check_robots($p_shop_path)
{
	$t_file = substr(DIR_FS_CATALOG, 0, (strlen(DIR_WS_CATALOG) * -1)) . '/robots.txt';
	
	// check if robots.txt is a regular file
	if(!is_file($t_file)) {
		return true;
	}
	
	$file = file_get_contents($t_file);
	$position = strpos($file, $p_shop_path.'admin/');
	if($position !== false) {
		return true;
	}

	return false;
}
