<?php
/* --------------------------------------------------------------
  PurposeWriteServiceFactory.php 2020-01-13
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\Factories;

use CI_DB_query_builder;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDatabaseWriterInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeWriteRepositoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeWriteServiceFactoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeWriteServiceInterface;
use Gambio\CookieConsentPanel\Services\Purposes\PurposeWriteService;
use Gambio\CookieConsentPanel\Services\Purposes\Repositories\PurposeDatabaseWriter;
use Gambio\CookieConsentPanel\Services\Purposes\Repositories\PurposeWriteRepository;

/**
 * Class PurposeWriteServiceFactory
 * @package Gambio\CookieConsentPanel\Services\Purposes\Factories
 */
class PurposeWriteServiceFactory implements PurposeWriteServiceFactoryInterface
{
    /**
     * @var CI_DB_query_builder
     */
    protected $queryBuilder;
    
    /**
     * @var PurposeWriteServiceInterface
     */
    protected $service;
    
    /**
     * @var PurposeWriteRepositoryInterface
     */
    protected $repository;
    
    /**
     * @var PurposeDatabaseWriterInterface
     */
    protected $writer;
    
    
    
    /**
     * PurposeWriteServiceFactory constructor.
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
    public function service(): PurposeWriteServiceInterface
    {
        if ($this->service === null) {
            
            $this->service = new PurposeWriteService($this->repository());
        }
        
        return $this->service;
    }
    
    
    /**
     * @return PurposeWriteRepositoryInterface
     */
    protected function repository(): PurposeWriteRepositoryInterface
    {
        if ($this->repository === null) {
            
            $this->repository = new PurposeWriteRepository($this->writer());
        }
        
        return $this->repository;
    }
    
    
    /**
     * @return PurposeDatabaseWriterInterface
     */
    protected function writer(): PurposeDatabaseWriterInterface
    {
        $lang = new \LanguageTextManager();
        if ($this->writer === null) {
    
            $this->writer = new PurposeDatabaseWriter($this->queryBuilder, $lang);
        }
        return $this->writer;
    }
}