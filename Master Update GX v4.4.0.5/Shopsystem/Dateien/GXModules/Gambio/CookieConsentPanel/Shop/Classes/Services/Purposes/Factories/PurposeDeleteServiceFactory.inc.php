<?php
/* --------------------------------------------------------------
  PurposeDeleteServiceFactory.php 2020-01-13
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\Factories;

use CI_DB_query_builder;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDatabaseDeleterInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDeleteRepositoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDeleteServiceFactoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDeleteServiceInterface;
use Gambio\CookieConsentPanel\Services\Purposes\PurposeDeleteService;
use Gambio\CookieConsentPanel\Services\Purposes\Repositories\PurposeDatabaseDeleter;
use Gambio\CookieConsentPanel\Services\Purposes\Repositories\PurposeDeleteRepository;
use StaticGXCoreLoader;

/**
 * Class PurposeDeleteServiceFactory
 * @package Gambio\CookieConsentPanel\Services\Purposes\Factories
 */
class PurposeDeleteServiceFactory implements PurposeDeleteServiceFactoryInterface
{
    /**
     * @var CI_DB_query_builder
     */
    protected $queryBuilder;
    
    /**
     * @var PurposeDeleteService
     */
    protected $service;
    
    /**
     * @var PurposeDeleteRepository
     */
    protected $repository;
    
    /**
     * @var PurposeDatabaseDeleter
     */
    protected $deleter;
    
    
    /**
     * PurposeDeleteServiceFactory constructor.
     *
     * @param CI_DB_query_builder $queryBuilder
     */
    public function __construct(CI_DB_query_builder $queryBuilder = null)
    {
        $this->queryBuilder = $queryBuilder ?: StaticGXCoreLoader::getDatabaseQueryBuilder();
    }
    
    /**
     * @inheritDoc
     */
    public function service(): PurposeDeleteServiceInterface
    {
        if ($this->service === null) {
    
            $this->service = new PurposeDeleteService($this->repository());
        }
        
        return $this->service;
    }
    
    
    /**
     * @return PurposeDeleteRepositoryInterface
     */
    protected function repository(): PurposeDeleteRepositoryInterface
    {
        if ($this->repository === null) {
    
            $this->repository = new PurposeDeleteRepository($this->deleter());
        }
        
        return $this->repository;
    }
    
    
    /**
     * @return PurposeDatabaseDeleterInterface
     */
    protected function deleter(): PurposeDatabaseDeleterInterface
    {
        if ($this->deleter === null) {
    
            $this->deleter = new PurposeDatabaseDeleter($this->queryBuilder);
        }
        
        return $this->deleter;
    }
}