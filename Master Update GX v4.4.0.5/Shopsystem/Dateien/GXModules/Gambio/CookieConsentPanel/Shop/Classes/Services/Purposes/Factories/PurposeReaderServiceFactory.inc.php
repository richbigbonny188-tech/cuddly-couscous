<?php
/* --------------------------------------------------------------
  PurposeReaderServiceFactory.php 2020-01-10
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\Factories;

use CI_DB_query_builder;
use Gambio\CookieConsentPanel\Services\Purposes\Helpers\CategoryCategoryIdMapper;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\CategoryCategoryIdMapperInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDatabaseReaderInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeFactoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeReaderRepositoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeReaderServiceFactoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeReaderServiceInterface;
use Gambio\CookieConsentPanel\Services\Purposes\PurposeReaderService;
use Gambio\CookieConsentPanel\Services\Purposes\Repositories\PurposeDatabaseReader;
use Gambio\CookieConsentPanel\Services\Purposes\Repositories\PurposeReaderRepository;
use Gambio\CookieConsentPanel\Services\Purposes\ValueObjects\LanguageCode;
use LanguageProvider;
use LanguageProviderInterface;

/**
 * Class PurposeReaderServiceFactory
 * @package Gambio\CookieConsentPanel\Services\Purposes\Factories
 */
class PurposeReaderServiceFactory implements PurposeReaderServiceFactoryInterface
{
    /**
     * @var CI_DB_query_builder
     */
    protected $queryBuilder;
    
    /**
     * @var PurposeReaderServiceInterface
     */
    protected $service;
    
    /**
     * @var PurposeReaderRepositoryInterface
     */
    protected $repository;
    
    /**
     * @var PurposeDatabaseReaderInterface
     */
    protected $reader;
    
    /**
     * @var CategoryCategoryIdMapper
     */
    protected $mapper;
    
    /**
     * @var LanguageCode
     */
    protected $languageCode;
    
    /**
     * @var PurposeFactory
     */
    protected $factory;
    
    /**
     * @var LanguageProvider
     */
    protected $languageProvider;
    
    
    /**
     * PurposeReaderServiceFactory constructor.
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
    public function service(): PurposeReaderServiceInterface
    {
        if ($this->service === null) {
    
            $this->service = new PurposeReaderService($this->repository());
        }
        
        return $this->service;
    }
    
    
    /**
     * @return PurposeReaderRepositoryInterface
     */
    protected function repository(): PurposeReaderRepositoryInterface
    {
        if ($this->repository === null) {
    
            $this->repository = new PurposeReaderRepository($this->reader(), $this->factory(), $this->mapper(), $this->languageProvider());
        }
        
        return $this->repository;
    }
    
    
    /**
     * @return PurposeDatabaseReaderInterface
     */
    protected function reader(): PurposeDatabaseReaderInterface
    {
        if ($this->reader === null) {
            
            $this->reader = new PurposeDatabaseReader($this->queryBuilder, $this->mapper());
        }
        
        return $this->reader;
    }
    
    
    /**
     * @return PurposeFactoryInterface
     */
    protected function factory(): PurposeFactoryInterface
    {
        if ($this->factory === null) {
            
            $this->factory = new PurposeFactory($this->mapper(), $this->languageCode());
        }
        
        return $this->factory;
    }
    
    
    /**
     * @return CategoryCategoryIdMapperInterface
     */
    protected function mapper(): CategoryCategoryIdMapperInterface
    {
        if ($this->mapper === null) {
            
            $this->mapper = new CategoryCategoryIdMapper;
        }
        
        return $this->mapper;
    }
    
    
    /**
     * @return LanguageCode
     */
    protected function languageCode(): LanguageCode
    {
        if ($this->languageCode === null) {
            
            $this->languageCode = new LanguageCode($_SESSION['language_code']);
        }
        
        return $this->languageCode;
    }
    
    
    /**
     * @return LanguageProviderInterface
     */
    protected function languageProvider(): LanguageProviderInterface
    {
        if ($this->languageProvider === null) {
    
            $this->languageProvider = new LanguageProvider($this->queryBuilder);
        }
        
        return $this->languageProvider;
    }
}