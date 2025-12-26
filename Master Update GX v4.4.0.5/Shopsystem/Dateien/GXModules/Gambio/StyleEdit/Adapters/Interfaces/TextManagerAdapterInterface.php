<?php
/*--------------------------------------------------------------------------------------------------
    TextManagerAdapterInterface.php 2020-10-26
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace GXModules\Gambio\StyleEdit\Adapters\Interfaces;

interface TextManagerAdapterInterface
{
    
    /**
     * @param string   $phrase
     * @param string   $section
     * @param int|null $languageId
     *
     * @return string
     */
    public function getPhraseText(string $phrase, string $section, int $languageId = null): string;
}