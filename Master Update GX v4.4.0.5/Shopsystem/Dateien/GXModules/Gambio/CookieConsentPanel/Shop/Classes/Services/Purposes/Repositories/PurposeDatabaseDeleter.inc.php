<?php
/* --------------------------------------------------------------
  PurposeDatabaseDeleter.php 2020-01-13
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\Repositories;

use Gambio\CookieConsentPanel\Services\Purposes\Exceptions\PurposeIsNotDeletableException;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDatabaseDeleterInterface;

/**
 * Class PurposeDatabaseDeleter
 * @package Gambio\CookieConsentPanel\Services\Purposes\Repositories
 */
class PurposeDatabaseDeleter implements PurposeDatabaseDeleterInterface
{
    protected const PURPOSE_TABLE = 'cookie_consent_panel_purposes';
    
    /**
     * @var \CI_DB_query_builder
     */
    protected $queryBuilder;
    
    
    /**
     * PurposeDatabaseDeleter constructor.
     *
     * @param \CI_DB_query_builder $queryBuilder
     */
    public function __construct(\CI_DB_query_builder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }
    
    
    /**
     * @inheritDoc
     */
    public function deleteByPurposeId(int $purposeId): void
    {
        if (!$this->isPurposeDeletableById($purposeId)) {
            
            throw new PurposeIsNotDeletableException('Purpose with id ' . $purposeId . ' is not deletable');
        }
        
        $this->queryBuilder->delete(self::PURPOSE_TABLE, '`purpose_id` = ' . $this->queryBuilder->escape($purposeId));
    }
    
    
    /**
     * @inheritDoc
     */
    public function deleteByPurposeAlias(string $alias): void
    {
        if (!$this->isPurposeDeletableByAlias($alias)) {
    
            throw new PurposeIsNotDeletableException('Purpose with the alias ' . $alias . ' is not deletable');
        }
        
        $this->queryBuilder->delete(self::PURPOSE_TABLE, '`purpose_alias` = ' . $this->queryBuilder->escape($alias));
    }
    
    
    /**
     * @param int $purposeId
     *
     * @return bool
     */
    protected function isPurposeDeletableById(int $purposeId): bool
    {
        $purposes = $this->queryBuilder->select()->from(self::PURPOSE_TABLE)->where('purpose_id', $purposeId)->get()->result_array();
        
        $result = true;
        
        foreach ($purposes as $purpose) {
            
            if ((int)$purpose['purpose_deletable'] === 0) {
                
                $result = false;
            }
        }
        
        return $result;
    }
    
    
    /**
     * @param string $alias
     *
     * @return bool
     */
    protected function isPurposeDeletableByAlias(string $alias): bool
    {
        $purposes = $this->queryBuilder->select()->from(self::PURPOSE_TABLE)->where('purpose_alias', $alias)->get()->result_array();
    
        $result = true;
    
        foreach ($purposes as $purpose) {
        
            if ((int)$purpose['purpose_deletable'] === 0) {
            
                $result = false;
            }
        }
    
        return $result;
    }
}