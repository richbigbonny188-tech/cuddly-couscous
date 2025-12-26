<?php
/* --------------------------------------------------------------
  PurposeUpdateServiceFactory.php 2020-01-13
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\Factories;

use CI_DB_query_builder;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDatabaseUpdaterInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeUpdateRepositoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeUpdateServiceFactoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeUpdateServiceInterface;
use Gambio\CookieConsentPanel\Services\Purposes\PurposeUpdateService;
use Gambio\CookieConsentPanel\Services\Purposes\Repositories\PurposeDatabaseUpdater;
use Gambio\CookieConsentPanel\Services\Purposes\Repositories\PurposeUpdateRepository;

/**
 * Class PurposeUpdateServiceFactory
 * @package Gambio\CookieConsentPanel\Services\Purposes\Factories
 */
class PurposeUpdateServiceFactory implements PurposeUpdateServiceFactoryInterface
{
    /**
     * @var PurposeUpdateService
     */
    protected $service;
    
    /**
     * @var PurposeUpdateRepository
     */
    protected $repository;
    
    /**
     * @var CI_DB_query_builder
     */
    protected $queryBuilder;
    
    /**
     * @var PurposeDatabaseUpdater
     */
    protected $updater;
    
    
    /**
     * PurposeUpdateServiceFactory constructor.
     *
     * @param CI_DB_query_builder $queryBuilder
     */
    public function __construct(CI_DB_query_builder $queryBuilder = null)
    {
        $this->queryBuilder = $queryBuilder ?: \StaticGXCoreLoader::getDatabaseQueryBuilder();
    }
    
    
    /**
     * @inheritDoc
     */
    public function service(): PurposeUpdateServiceInterface
    {
        if ($this->service === null) {
    
            $this->service = new PurposeUpdateService($this->repository());
        }
        
        return $this->service;
    }
    
    
    /**
     * @return PurposeUpdateRepositoryInterface
     */
    protected function repository(): PurposeUpdateRepositoryInterface
    {
        if ($this->repository === null) {
            
            $this->repository = new PurposeUpdateRepository($this->updater());
        }
    
        return $this->repository;
    }
    
    
    /**
     * @return PurposeDatabaseUpdaterInterface
     */
    protected function updater(): PurposeDatabaseUpdaterInterface
    {
        if ($this->updater === null) {
    
            $this->updater = new PurposeDatabaseUpdater($this->queryBuilder);
        }
        
        return $this->updater;
    }
}