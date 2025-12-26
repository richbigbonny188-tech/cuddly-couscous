<?php
/* --------------------------------------------------------------
  PurposeDeleteRepository.php 2020-01-13
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\Repositories;

use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDatabaseDeleterInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDeleteRepositoryInterface;

/**
 * Class PurposeDeleteRepository
 * @package Gambio\CookieConsentPanel\Services\Purposes\Repositories
 */
class PurposeDeleteRepository implements PurposeDeleteRepositoryInterface
{
    /**
     * @var PurposeDatabaseDeleterInterface
     */
    protected $deleter;
    
    
    /**
     * PurposeDeleteRepository constructor.
     *
     * @param PurposeDatabaseDeleterInterface $deleter
     */
    public function __construct(PurposeDatabaseDeleterInterface $deleter)
    {
        $this->deleter = $deleter;
    }
    
    
    /**
     * @inheritDoc
     */
    public function deleteByPurposeId(int $purposeId): void
    {
        $this->deleter->deleteByPurposeId($purposeId);
    }
    
    
    /**
     * @inheritDoc
     */
    public function deleteByPurposeAlias(string $alias): void
    {
        $this->deleter->deleteByPurposeAlias($alias);
    }
}