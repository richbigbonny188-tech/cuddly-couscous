<?php
/* --------------------------------------------------------------
  PurposeWriteService.php 2020-01-13
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes;

use CookieConsentPurposeDTO;
use CookieConsentPurposeInterface;
use CookieConsentPurposeReaderServiceInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeWriterDtoInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeWriteRepositoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeWriteServiceInterface;

/**
 * Class PurposeWriteService
 * @package Gambio\CookieConsentPanel\Services\Purposes
 */
class PurposeWriteService implements PurposeWriteServiceInterface, CookieConsentPurposeReaderServiceInterface
{
    /**
     * @var PurposeWriteRepositoryInterface
     */
    protected $repository;
    
    
    /**
     * PurposeWriteService constructor.
     *
     * @param PurposeWriteRepositoryInterface $repository
     */
    public function __construct(PurposeWriteRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
    
    
    /**
     * @inheritDoc
     */
    public function store(PurposeWriterDtoInterface $dto): int
    {
        return $this->repository->store($dto);
    }
    
    
    /**
     * @inheritDoc
     */
    public function getCookieConsentPurposeBy(CookieConsentPurposeDTO $purposeDTO): CookieConsentPurposeInterface
    {
        return $this->repository->getCookieConsentPurposeBy($purposeDTO);
    }
}