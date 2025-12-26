<?php
/* --------------------------------------------------------------
  PurposeUpdateService.php 2020-01-13
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes;

use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeUpdateDtoInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeUpdateRepositoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeUpdateServiceInterface;

/**
 * Class PurposeUpdateService
 * @package Gambio\CookieConsentPanel\Services\Purposes
 */
class PurposeUpdateService implements PurposeUpdateServiceInterface
{
    /**
     * @var PurposeUpdateRepositoryInterface
     */
    protected $repository;
    
    
    /**
     * PurposeUpdateService constructor.
     *
     * @param PurposeUpdateRepositoryInterface $repository
     */
    public function __construct(PurposeUpdateRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
    
    
    /**
     * @inheritDoc
     */
    public function update(PurposeUpdateDtoInterface $dto): void
    {
        $this->repository->update($dto);
    }
    
    
    /**
     * @inheritDoc
     */
    public function updateStatus(int $id, bool $status)
    {
        $this->repository->updateStatus($id, $status);
    }
}