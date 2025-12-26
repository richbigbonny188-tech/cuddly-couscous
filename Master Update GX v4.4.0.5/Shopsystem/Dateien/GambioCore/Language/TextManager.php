<?php
/* --------------------------------------------------------------
   TextManager.php 2020-04-16
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\Language;

/**
 * Class TextManager
 *
 * @package Gambio\Core\Language
 */
interface TextManager
{
    /**
     * @param string   $section
     * @param int|null $languageId
     *
     * @return string[]
     */
    public function getSectionPhrases(string $section, int $languageId = null): array;
    
    
    /**
     * @param string   $phrase
     * @param string   $section
     * @param int|null $languageId
     *
     * @return string
     */
    public function getPhraseText(string $phrase, string $section, int $languageId = null): string;
}