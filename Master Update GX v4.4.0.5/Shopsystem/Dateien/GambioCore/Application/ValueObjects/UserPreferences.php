<?php
/* --------------------------------------------------------------
 UserPreferences.php 2020-04-16
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\ValueObjects;

use Gambio\Core\Application\Shared\HasLanguageId;
use Gambio\Core\Application\Shared\HasUserInformation;

/**
 * Class UserPreferences
 * @package Gambio\Core\Application\ValueObjects
 */
class UserPreferences implements HasUserInformation, HasLanguageId
{
    /**
     * @var int|null
     */
    private $userId;
    
    /**
     * @var int
     */
    private $languageId;
    
    
    /**
     * UserPreferences constructor.
     *
     * @param int|null $customerId
     * @param int      $languageId
     */
    public function __construct(?int $customerId, int $languageId)
    {
        $this->userId     = $customerId;
        $this->languageId = $languageId;
    }
    
    
    /**
     * @inheritDoc
     */
    public function userId(): ?int
    {
        return $this->userId;
    }
    
    
    /**
     * @inheritDoc
     */
    public function isAuthenticated(): bool
    {
        return null !== $this->userId;
    }
    
    
    /**
     * @inheritDoc
     */
    public function languageId(): int
    {
        return $this->languageId;
    }
}