<?php
/* --------------------------------------------------------------
 LanguageId.php 2020-09-14
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Shared;

/**
 * Interface HasLanguageId
 * @package Gambio\Core\Contracts\Shared
 */
interface HasLanguageId
{
    /**
     * Returns a language id of the shop system.
     *
     * @return int
     */
    public function languageId(): int;
}