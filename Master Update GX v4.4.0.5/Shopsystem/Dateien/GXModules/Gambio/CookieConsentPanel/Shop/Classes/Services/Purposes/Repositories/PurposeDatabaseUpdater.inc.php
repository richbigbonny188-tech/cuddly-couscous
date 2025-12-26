<?php
/* --------------------------------------------------------------
  PurposeDatabaseUpdater.php 2020-05-06
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\Repositories;

use CI_DB_query_builder;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDatabaseUpdaterInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeUpdateDtoInterface;

/**
 * Class PurposeDatabaseUpdater
 * @package Gambio\CookieConsentPanel\Services\Purposes\Repositories
 */
class PurposeDatabaseUpdater implements PurposeDatabaseUpdaterInterface
{
    protected const PURPOSE_TABLE = 'cookie_consent_panel_purposes';
    
    /**
     * @var CI_DB_query_builder
     */
    protected $queryBuilder;
    
    
    /**
     * PurposeDatabaseUpdater constructor.
     *
     * @param CI_DB_query_builder $queryBuilder
     */
    public function __construct(CI_DB_query_builder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }
    
    
    /**
     * @inheritDoc
     */
    public function update(PurposeUpdateDtoInterface $dto): void
    {
        if (!empty($dto->alias())) {
            
            $this->updateOnAlias($dto);
            
            return;
        }
        
        $this->replaceOnId($dto);
    }


    /**
     * @param int $languageId
     *
     * @return bool
     */

    protected function languageExists(int $languageId)
    {
        $result = $this->queryBuilder->select('language_id')->from(self::PURPOSE_TABLE)->get();
        if($result) {
            return true;
        };
        return false;
    }

    
    /**
     * @param PurposeUpdateDtoInterface $dto
     */
    protected function updateOnAlias(PurposeUpdateDtoInterface $dto)
    {
        foreach ($dto->names() as $languageId => $name) {

            $data = [
                'category_id'         => $dto->categoryId(),
                'purpose_description' => $dto->descriptions()[$languageId],
                'purpose_name'        => $dto->names()[$languageId],
                'purpose_status'      => (int)$dto->status(),
            ];

            $where = [
                'purpose_alias' => $dto->alias(),
                'language_id'   => $languageId
            ];

            if($this->languageExists($languageId))
            {
                $this->queryBuilder->update(self::PURPOSE_TABLE,$data, $where);
            }
            else {
                $this->queryBuilder->insert(self::PURPOSE_TABLE,array_merge($where,$data));
            }

        }
    }
    
    
    /**
     * @param PurposeUpdateDtoInterface $dto
     */
    protected function replaceOnId(PurposeUpdateDtoInterface $dto)
    {
        $result = $this->queryBuilder
                       ->select_min("purpose_deletable")
                       ->from(self::PURPOSE_TABLE)
                       ->where(['purpose_id'=>$dto->id()])
                       ->get()
                       ->result_array();

        $purpose_deletable = (int)$result[0]['purpose_deletable'] ?? 0;
        foreach ($dto->names() as $languageId => $name) {
            $this->queryBuilder->replace(self::PURPOSE_TABLE,
                [
                    'purpose_id'          => $dto->id(),
                    'language_id'         => $languageId,
                    'purpose_name'        => $dto->name($languageId) ?? '',
                    'category_id'         => $dto->categoryId(),
                    'purpose_description' => $dto->description($languageId) ?? '',
                    'purpose_status'      => (int)$dto->status(),
                    'purpose_deletable'   => $purpose_deletable
                ]);
        }
    }
    
    
    /**
     * @inheritDoc
     */
    public function updateStatus(int $id, bool $status)
    {
        $this->queryBuilder->update(self::PURPOSE_TABLE, ['purpose_status'=>$status], ['purpose_id'=>$id]);
    }
}