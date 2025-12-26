<?php
/* --------------------------------------------------------------
  PurposeDeleteService.php 2020-01-13
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes;

use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDeleteRepositoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDeleteServiceInterface;

/**
 * Class PurposeDeleteService
 * @package Gambio\CookieConsentPanel\Services\Purposes
 */
class PurposeDeleteService implements PurposeDeleteServiceInterface
{
    /**
     * @var PurposeDeleteRepositoryInterface
     */
    protected $repository;
    
    
    /**
     * PurposeDeleteService constructor.
     *
     * @param PurposeDeleteRepositoryInterface $repository
     */
    public function __construct(PurposeDeleteRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
    
    
    /**
     * @inheritDoc
     */
    public function deleteByPurposeId(int $purposeId): void
    {
        $this->repository->deleteByPurposeId($purposeId);
    }
    
    
    /**
     * @inheritDoc
     */
    public function deleteByPurposeAlias(string $alias): void
    {
        $this->repository->deleteByPurposeAlias($alias);
    }
}