<?php
/* --------------------------------------------------------------
  PurposeDatabaseReader.php 2020-01-10
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\Repositories;

use CI_DB_query_builder;
use Gambio\CookieConsentPanel\Services\Purposes\DataTransferObjects\PurposeReaderDto;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\CategoryCategoryIdMapperInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDatabaseReaderInterface;

/**
 * Class PurposeDatabaseReader
 * @package Gambio\CookieConsentPanel\Services\Purposes\Repositories
 */
class PurposeDatabaseReader implements PurposeDatabaseReaderInterface
{
    protected const PURPOSES_TABLE = 'cookie_consent_panel_purposes';
    
    /**
     * @var CI_DB_query_builder
     */
    protected $queryBuilder;
    /**
     * @var CategoryCategoryIdMapperInterface
     */
    private $categoryMapper;
    
    
    /**
     * PurposeDatabaseReader constructor.
     *
     * @param CI_DB_query_builder               $queryBuilder
     * @param CategoryCategoryIdMapperInterface $categoryMapper
     */
    public function __construct(CI_DB_query_builder $queryBuilder, CategoryCategoryIdMapperInterface $categoryMapper)
    {
        $this->queryBuilder = $queryBuilder;
        $this->categoryMapper = $categoryMapper;
    }
    
    
    /**
     * @inheritDoc
     */
    public function activePurposes(int $languageId): array
    {
        $data = $this->baseQuery($languageId)->where('purpose_status', '1')->get()->result_array();
        
        return $this->mapToDto($data);
    }
    
    
    /**
     * @param int $languageId
     *
     * @return CI_DB_query_builder
     */
    protected function baseQuery(int $languageId = null): CI_DB_query_builder
    {
        if($languageId) {
            return $this->queryBuilder->select()->from(self::PURPOSES_TABLE)->where('language_id', $languageId);
        } else {
            return $this->queryBuilder->select()->from(self::PURPOSES_TABLE);
        }
    }
    
    
    /**
     * @param $data
     *
     * @return array
     */
    protected function mapToDto($data)
    {
        $result  = [];
        $records = [];
        foreach ($data as $entry) {
            
            if (array_key_exists((int)$entry['purpose_id'], $records)) {
                $record = $records[(int)$entry['purpose_id']];
            } else {
                $record = [
                    'purpose_name'        => [],
                    'purpose_description' => []
                ];
            }

            $record['category_id']                               = (int)$entry['category_id'];
            $record['purpose_description'][(int)$entry['language_id']] = $entry['purpose_description'];
            $record['purpose_name'][(int)$entry['language_id']]        = $entry['purpose_name'];
            $record['purpose_status']                            = (bool)$entry['purpose_status'];
            $record['purpose_deletable']                         = (bool)$entry['purpose_deletable'];
            $record['purpose_alias']                             = $entry['purpose_alias']?: null;
            $record['purpose_id']                                = $entry['purpose_id'];
            $records[(int)$entry['purpose_id']]                       = $record;
        }

        foreach($records as $record){
            
            $result[] = new PurposeReaderDto(
                (int)$record['category_id'],
                [],
                $record['purpose_description'],
                $record['purpose_name'],
                $record['purpose_status'],
                $record['purpose_deletable'],
                $record['purpose_alias'],
                $record['purpose_id']
            );
            
        }
        
        return $result;
    }
    
    
    /**
     * @inheritDoc
     */
    public function allPurposes(): array
    {
        $data = $this->baseQuery()->get()->result_array();
        
        return $this->mapToDto($data);
    }
}