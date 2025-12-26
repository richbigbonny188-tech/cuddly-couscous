<?php
/*--------------------------------------------------------------
   google_services_repair.php 2022-02-01
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2022 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

clearstatcache(true);

// check if Google Services 3 exists
if (file_exists(DIR_FS_CATALOG . 'GXModules/Gambio/Google/ECommerce')
    && file_exists(DIR_FS_CATALOG . 'GXModules/Gambio/GoogleECommerce')) {
    
    // repair menu
    if (file_exists(DIR_FS_CATALOG . 'GXModules/Gambio/Google/Admin/Menu/menu_google.xml')) {
        $success = @unlink(DIR_FS_CATALOG . 'GXModules/Gambio/Google/Admin/Menu/menu_google.xml');
        
        if ($success) {
            $xml = <<<'XML'
<?xml version="1.0"?>
<!-- {load_language_text section="admin_menu" use_fallback=(SHOW_UNTRANSLATED_MENUITEMS|defined)?$smarty.const.SHOW_UNTRANSLATED_MENUITEMS:'false'} -->

<admin_menu>
	<menugroup id="BOX_HEADING_GOOGLE" sort="35" class="fa fa-google" title="{$txt.BOX_HEADING_GOOGLE|escape}">
		<menuitem sort="10" link="admin.php" link_param="do=GoogleAccounts" title="{$txt.BOX_YOUR_ACCOUNTS|escape}" />
		<menuitem sort="20" link="admin.php" link_param="do=GoogleFeeds" title="{$txt.BOX_YOUR_FEEDS|escape}" />
		<menuitem sort="30" link="admin.php" link_param="do=GoogleAds" title="{$txt.BOX_YOUR_CAMPAIGNS|escape}" />
		<menuitem sort="40" link="admin.php" link_param="do=GoogleAnalyticsAdmin" title="{$txt.BOX_ANALYTICS|escape}" />
	</menugroup>
</admin_menu>
XML;
            
            file_put_contents(DIR_FS_CATALOG . 'GXModules/Gambio/Google/Admin/Menu/menu_google.xml', $xml);
            @chmod(DIR_FS_CATALOG . 'GXModules/Gambio/Google/Admin/Menu/menu_google.xml', 0777);
        }
    }
}
