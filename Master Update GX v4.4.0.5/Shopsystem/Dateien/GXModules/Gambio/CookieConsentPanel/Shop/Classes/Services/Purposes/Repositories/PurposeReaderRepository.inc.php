<?php
/* --------------------------------------------------------------
  PurposeReaderRepository.php 2020-01-10
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\Repositories;

use Gambio\CookieConsentPanel\Services\Purposes\DataTransferObjects\PurposeReaderDto;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\CategoryCategoryIdMapperInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDatabaseReaderInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeFactoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeReaderRepositoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\ValueObjects\LanguageCode;
use LanguageProviderInterface;

/**
 * Class PurposeReaderRepository
 * @package Gambio\CookieConsentPanel\Services\Purposes\Repositories
 */
class PurposeReaderRepository implements PurposeReaderRepositoryInterface
{
    /**
     * @var PurposeFactoryInterface
     */
    protected $factory;
    /**
     * @var CategoryCategoryIdMapperInterface
     */
    protected $mapper;
    /**
     * @var PurposeDatabaseReaderInterface
     */
    protected $reader;
    /**
     * @var LanguageProviderInterface
     */
    private $languageProvider;
    
    
    /**
     * PurposeReaderRepository constructor.
     *
     * @param PurposeDatabaseReaderInterface    $reader
     * @param PurposeFactoryInterface           $factory
     * @param CategoryCategoryIdMapperInterface $mapper
     * @param LanguageProviderInterface         $languageProvider
     */
    public function __construct(
        PurposeDatabaseReaderInterface $reader,
        PurposeFactoryInterface $factory,
        CategoryCategoryIdMapperInterface $mapper,
        LanguageProviderInterface $languageProvider
    ) {
        $this->reader           = $reader;
        $this->factory          = $factory;
        $this->mapper           = $mapper;
        $this->languageProvider = $languageProvider;
    }
    
    
    /**
     * @inheritDoc
     */
    public function activePurposes(int $languageId): array
    {
        $result = [];
        $data   = $this->reader->activePurposes($languageId);
    
        if (count($data)) {
        
            foreach ($data as $entry) {
            
                $result[] = $this->factory->create($entry);
            }
        }
    
        return $result;
    }
    
    
    /**
     * @inheritDoc
     */
    public function allPurposes(): array
    {
        $result = [];
        $data   = $this->reader->allPurposes();
    
        if (count($data)) {
        
            foreach ($data as $entry) {
            
                $result[] = $this->factory->create($entry);
            }
        }
    
        return $result;
    }
    
    
    /**
     * @inheritDoc
     */
    public function categories(int $languageId): array
    {
        $languageCode = $this->languageProvider->getCodeById(new \IdType($languageId));
        $languageCode = new LanguageCode($languageCode->asString());
        
        return $this->mapper->allCategories($languageCode);
    }
}