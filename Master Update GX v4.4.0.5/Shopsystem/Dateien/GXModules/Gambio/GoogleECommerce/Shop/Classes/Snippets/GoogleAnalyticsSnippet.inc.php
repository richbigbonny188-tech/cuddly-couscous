<?php

/* --------------------------------------------------------------
   GoogleAnalyticsSnippet.inc.php 2020-09-24
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2018 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class representing the Google Analytics snippet content view overload
 */
class GoogleAnalyticsSnippet extends ContentView
{
    /**
     * Create instance
     *
     * @param string $template Template
     *
     * @throws Exception Missing template argument
     */
    public function __construct($template)
    {
        if (!$template) {
            throw new Exception('Missing template argument');
        }
        
        parent::__construct();
        
        $this->set_template_dir(DIR_FS_CATALOG . '/GXModules/Gambio/GoogleECommerce/Shop/Templates/All/module/');
        $this->set_content_template("google_analytics_snippet_{$template}.html");
        $this->set_flat_assigns(true);
    }
}