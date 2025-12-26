<?php
/* --------------------------------------------------------------
  PurposeWriteRepository.php 2020-01-13
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\Repositories;

use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDatabaseWriterInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeWriterDtoInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeWriteRepositoryInterface;

/**
 * Class PurposeWriteRepository
 * @package Gambio\CookieConsentPanel\Services\Purposes\Repositories
 */
class PurposeWriteRepository implements PurposeWriteRepositoryInterface
{
    /**
     * @var PurposeDatabaseWriterInterface
     */
    protected $writer;
    
    
    /**
     * PurposeWriteRepository constructor.
     *
     * @param PurposeDatabaseWriterInterface $writer
     */
    public function __construct(PurposeDatabaseWriterInterface $writer)
    {
        $this->writer = $writer;
    }
    
    
    /**
     * @inheritDoc
     */
    public function store(PurposeWriterDtoInterface $data): int
    {
        return $this->writer->store($data);
    }
    
    
    /**
     * @inheritDoc
     */
    public function getCookieConsentPurposeBy(\CookieConsentPurposeDTO $purposeDTO)
    {
        return $this->writer->getCookieConsentPurposeBy($purposeDTO);
    }
}