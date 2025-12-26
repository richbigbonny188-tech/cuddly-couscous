<?php
/* --------------------------------------------------------------
  PurposeUpdateRepository.php 2020-01-13
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\Repositories;

use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDatabaseUpdaterInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeUpdateDtoInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeUpdateRepositoryInterface;

/**
 * Class PurposeUpdateRepository
 * @package Gambio\CookieConsentPanel\Services\Purposes\Repositories
 */
class PurposeUpdateRepository implements PurposeUpdateRepositoryInterface
{
    /**
     * @var PurposeDatabaseUpdaterInterface
     */
    protected $updater;
    
    
    /**
     * PurposeUpdateRepository constructor.
     *
     * @param PurposeDatabaseUpdaterInterface $updater
     */
    public function __construct(PurposeDatabaseUpdaterInterface $updater)
    {
        $this->updater = $updater;
    }
    
    
    /**
     * @inheritDoc
     */
    public function update(PurposeUpdateDtoInterface $dto): void
    {
        $this->updater->update($dto);
    }
    
    
    /**
     * @inheritDoc
     */
    public function updateStatus(int $id, bool $status)
    {
        $this->updater->updateStatus($id, $status);
    }
}