<?php
/* --------------------------------------------------------------
 GoogleTranslationsLoader.php 2020-07-10
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

namespace GXModules\Gambio\Google\Admin\Module;

use Gambio\Core\Language\TextManager;
use Gambio\Core\TemplateEngine\LayoutData;
use Gambio\Core\TemplateEngine\Loader;

/**
 * Class GoogleTranslationsLoader
 * @package GXModules\Gambio\Google\Admin\Plugin
 */
class GoogleTranslationsLoader implements Loader
{
    /**
     * @var TextManager
     */
    private $textManager;
    
    
    /**
     * GoogleTranslationsLoader constructor.
     *
     * @param TextManager $textManager
     */
    public function __construct(TextManager $textManager)
    {
        $this->textManager = $textManager;
    }
    
    
    /**
     * @inheritDoc
     */
    public function load(LayoutData $data): void
    {
        $data->assign(
            'google_txt',
            [
                'connected_title'    => $this->textManager->getPhraseText(
                    'TEXT_GOOGLE_ADWORDS_CONNECTED',
                    'admin_general'
                ),
                'disconnected_title' => $this->textManager->getPhraseText(
                    'TEXT_GOOGLE_ADWORDS_DISCONNECTED',
                    'admin_general'
                ),
            ]
        );
    }
}