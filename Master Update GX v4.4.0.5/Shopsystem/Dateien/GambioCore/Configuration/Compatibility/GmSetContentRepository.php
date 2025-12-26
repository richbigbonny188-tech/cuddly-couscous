<?php
/* --------------------------------------------------------------
 LegacyConfigurationRepository.php 2020-01-08
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 08 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Configuration\Compatibility;

/**
 * Interface LegacyConfigurationRepository
 * @package    Gambio\Core\Configuration
 *
 * @deprecated This repository was created to abstract the gm_set_content functionality and shouldn't be used in new
 *             domains.
 */
interface GmSetContentRepository
{
    /**
     * Abstraction of gm_set_conf.
     *
     * @param string $key
     * @param string $value
     * @param int    $languageId
     * @param int    $groupId
     */
    public function update(string $key, string $value, int $languageId, int $groupId = 0): void;
}