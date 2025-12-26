<?php
/* --------------------------------------------------------------
  PurposeReaderService.php 2020-01-10
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes;

use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\CategoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeReaderServiceInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeReaderRepositoryInterface;

/**
 * Class PurposeReaderService
 * @package Gambio\CookieConsentPanel\Services\Purposes
 */
class PurposeReaderService implements PurposeReaderServiceInterface
{
    /**
     * @var PurposeReaderRepositoryInterface
     */
    protected $repository;
    
    
    /**
     * PurposeReaderService constructor.
     *
     * @param PurposeReaderRepositoryInterface $repository
     */
    public function __construct(PurposeReaderRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
    
    
    /**
     * @inheritDoc
     */
    public function categories(int $languageId): array
    {
        return $this->repository->categories($languageId);
    }
    
    
    /**
     * @inheritDoc
     */
    public function activePurposes(int $languageId): array
    {
        return $this->repository->activePurposes($languageId);
    }
    
    
    /**
     * @inheritDoc
     */
    public function allPurposes(): array
    {
        return $this->repository->allPurposes();
    }
}