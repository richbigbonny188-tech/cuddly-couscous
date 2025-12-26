<?php
/* --------------------------------------------------------------
   TextManager.php 2020-04-16
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2019 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\Language\Services;

use Gambio\Core\Application\Shared\HasLanguageId;
use Gambio\Core\Language;
use Gambio\Core\Language\Model\LanguageId;
use Gambio\Core\Language\TextPhraseRepository;

/**
 * Class TextManager
 *
 * @package Gambio\Core\Language
 */
class TextManager implements Language\TextManager
{
    /**
     * @var TextPhraseRepository
     */
    private $repository;
    
    /**
     * @var int
     */
    private $defaultLanguageId;
    
    
    /**
     * TextManager constructor.
     *
     * @param TextPhraseRepository $repository
     * @param HasLanguageId        $userPreferences
     */
    public function __construct(TextPhraseRepository $repository, HasLanguageId $userPreferences)
    {
        $this->repository        = $repository;
        $this->defaultLanguageId = $userPreferences->languageId();
    }
    
    
    /**
     * @inheritDoc
     */
    public function getSectionPhrases(string $section, int $languageId = null): array
    {
        $languageId = $languageId ?? $this->defaultLanguageId;
        
        return $this->repository->getSectionPhrases($section, LanguageId::create($languageId));
    }
    
    
    /**
     * @inheritDoc
     */
    public function getPhraseText(string $phrase, string $section, int $languageId = null): string
    {
        $languageId = $languageId ?? $this->defaultLanguageId;
        
        return $this->repository->getPhraseText($phrase, $section, LanguageId::create($languageId));
    }
}