<?php
/*--------------------------------------------------------------------------------------------------
    TextManagerAdapter.php 2020-10-28
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace GXModules\Gambio\StyleEdit\Adapters;

use Gambio\Core\Language\TextManager;
use Gambio\Core\Language\TextManager as TextManagerInterface;
use GXModules\Gambio\StyleEdit\Adapters\Interfaces\TextManagerAdapterInterface;

class TextManagerAdapter implements TextManagerAdapterInterface
{
    /**
     * @var TextManager
     */
    protected $textManager;
    
    
    public function __construct(TextManager $textManager)
    {
        $this->textManager = $textManager;
    }
    
    
    public static function create()
    {
        $textManager = \LegacyDependencyContainer::getInstance()->get(TextManagerInterface::class);
    
        return new self($textManager);
    }
    
    
    /**
     * @inheritcDoc
     */
    public function getPhraseText(string $phrase, string $section, int $languageId = null): string
    {
        return $this->textManager->getPhraseText($phrase, $section, $languageId);
    }
}