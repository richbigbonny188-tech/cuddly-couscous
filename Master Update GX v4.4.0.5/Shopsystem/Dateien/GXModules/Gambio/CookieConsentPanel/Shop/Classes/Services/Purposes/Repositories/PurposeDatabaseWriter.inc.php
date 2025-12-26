<?php
/* --------------------------------------------------------------
  PurposeDatabaseWriter.php 2020-01-13
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\Repositories;

use CI_DB_query_builder;
use CookieConsentPurposeInterface;
use Gambio\CookieConsentPanel\Services\Purposes\DataTransferObjects\PurposeWriterDto;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDatabaseWriterInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeWriterDtoInterface;

/**
 * Class PurposeDatabaseWriter
 * @package Gambio\CookieConsentPanel\Services\Purposes\Repositories
 */
class PurposeDatabaseWriter implements PurposeDatabaseWriterInterface
{
    protected const PURPOSE_TABLE = 'cookie_consent_panel_purposes';
    
    /**
     * @var CI_DB_query_builder
     */
    protected $queryBuilder;
    /**
     * @var \LanguageTextManager
     */
    private $languageTextManager;
    
    
    /**
     * PurposeDatabaseWriter constructor.
     *
     * @param CI_DB_query_builder  $queryBuilder
     * @param \LanguageTextManager $languageTextManager
     */
    public function __construct(CI_DB_query_builder $queryBuilder, \LanguageTextManager $languageTextManager)
    {
        $this->queryBuilder        = $queryBuilder;
        $this->languageTextManager = $languageTextManager;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getCookieConsentPurposeBy(\CookieConsentPurposeDTO $purposeDTO)
    {
        $data = $this->queryBuilder->select('purpose_id')
            ->distinct()
            ->from(self::PURPOSE_TABLE)
            ->where(['purpose_alias' => $purposeDTO->alias()])
            ->get()
            ->result_array();
        
        if (count($data)) {
            return $this->makeCookie((int)$data[0]['purpose_id']);
        }
        
        $languages    = $this->listLanguagesId();
        $descriptions = [];
        $names        = [];
        
        list($descriptionSection, $descriptionId) = explode('.', $purposeDTO->description());
        list($nameSection, $nameId) = explode('.', $purposeDTO->name());
        
        foreach ($languages as $langId) {
            $descriptions[$langId] = $this->languageTextManager->get_text($descriptionId,
                                                                          $descriptionSection,
                                                                          $langId);
            
            $names[$langId] = $this->languageTextManager->get_text($nameId, $nameSection, $langId);
        }
        
        return $this->makeCookie($this->store(new PurposeWriterDto($purposeDTO->category(),
                                                                   $descriptions,
                                                                   $names,
                                                                   $purposeDTO->status(),
                                                                   false,
                                                                   $purposeDTO->alias())));
    }
    
    
    /**
     * @return int[]
     */
    protected function listLanguagesId(): array
    {
        $result = [];
        $data   = $this->queryBuilder->select('language_id ')
            ->distinct()
            ->from(self::PURPOSE_TABLE)
            ->get()
            ->result_array();
        foreach ($data as $record) {
            $result[] = (int)$record['language_id'];
        }
        
        return $result;
    }
    
    
    /**
     * @param int $code
     *
     * @return CookieConsentPurposeInterface
     */
    protected function makeCookie(int $code): CookieConsentPurposeInterface
    {
        
        return new class($code) implements CookieConsentPurposeInterface {
            private $code;
            
            
            /**
             *  constructor.
             *
             * @param $code
             */
            public function __construct($code)
            {
                $this->code = $code;
            }
            
            
            /**
             * @inheritDoc
             */
            public function purposeCode(): int
            {
                return $this->code;
            }
        };
    }
    
    
    /**
     * @inheritDoc
     */
    public function store(PurposeWriterDtoInterface $dataTransferObject): int
    {
        $purposeId = $this->nextPurposeId();
        
        foreach ($dataTransferObject->name() as $languageId => $name) {
            
            $data = [
                'purpose_id'          => $purposeId,
                'language_id'         => $languageId,
                'purpose_name'        => $name,
                'purpose_description' => $dataTransferObject->description()[$languageId],
                'category_id'         => $dataTransferObject->category(),
                'purpose_alias'       => $dataTransferObject->alias(),
                'purpose_status'      => $dataTransferObject->status(),
                'purpose_deletable'   => $dataTransferObject->deletable(),
            ];
            
            $this->queryBuilder->insert(self::PURPOSE_TABLE, $data);
        }
        
        return $purposeId;
    }
    
    
    /**
     * @return int
     */
    protected function nextPurposeId(): int
    {
        $maxPurposeId = $this->queryBuilder->select('MAX(`purpose_id`) as max')
            ->from(self::PURPOSE_TABLE)
            ->get()
            ->result_array();
        $maxPurposeId = array_shift($maxPurposeId)['max'];
        $maxPurposeId = $maxPurposeId === null ? 0 : $maxPurposeId;
        
        return ++$maxPurposeId;
    }
}