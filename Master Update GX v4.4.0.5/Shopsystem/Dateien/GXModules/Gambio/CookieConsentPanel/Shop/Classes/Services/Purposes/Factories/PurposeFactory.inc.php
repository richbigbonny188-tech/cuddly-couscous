<?php
/* --------------------------------------------------------------
  PurposeFactory.php 2020-01-10
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\Factories;

use Gambio\CookieConsentPanel\Services\Purposes\Builders\PurposeBuilder;
use Gambio\CookieConsentPanel\Services\Purposes\DataTransferObjects\PurposeReaderDto;
use Gambio\CookieConsentPanel\Services\Purposes\Exceptions\UnfinishedBuildException;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\CategoryCategoryIdMapperInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeBuilderInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDtoInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeFactoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeInterface;
use Gambio\CookieConsentPanel\Services\Purposes\ValueObjects\LanguageCode;

/**
 * Class PurposeFactory
 * @package Gambio\CookieConsentPanel\Services\Purposes\Factories
 */
class PurposeFactory implements PurposeFactoryInterface
{
    /**
     * @var LanguageCode
     */
    protected $code;
    /**
     * @var PurposeDtoInterface
     */
    protected $dto;
    /**
     * @var CategoryCategoryIdMapperInterface
     */
    protected $mapper;
    
    
    /**
     * PurposeFactory constructor.
     *
     * @param CategoryCategoryIdMapperInterface $mapper
     * @param LanguageCode                      $code
     */
    public function __construct(CategoryCategoryIdMapperInterface $mapper, LanguageCode $code)
    {
        $this->mapper = $mapper;
        $this->code   = $code;
    }
    
    
    /**
     * @inheritDoc
     *
     * @param PurposeDtoInterface $dto
     *
     * @return PurposeInterface
     * @throws UnfinishedBuildException
     */
    public function create(PurposeDtoInterface $dto): PurposeInterface
    {
        $this->dto = $dto;
        
        return $this->builder()
            ->withStatus($dto->status())
            ->withNames($dto->names())
            ->withId($dto->id())
            ->withDescriptions($dto->descriptions())
            ->withDeletable($dto->deletable())
            ->withCategoryId($dto->categoryId(), $this->code)
            ->withAlias($dto->alias())
            ->build();
    }
    
    
    /**
     * @return PurposeBuilderInterface
     */
    protected function builder(): PurposeBuilderInterface
    {
        return PurposeBuilder::create($this->mapper);
    }
}