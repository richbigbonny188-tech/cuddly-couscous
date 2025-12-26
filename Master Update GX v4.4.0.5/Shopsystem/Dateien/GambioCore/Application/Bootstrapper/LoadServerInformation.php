<?php
/* --------------------------------------------------------------
 LoadServerInformation.php 2020-02-20
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Bootstrapper;

use Gambio\Core\Application\ValueObjects\ServerInformation;
use Gambio\Core\Application\Contracts\Application;
use Gambio\Core\Application\Contracts\Bootstrapper;

/**
 * Class LoadServerInformation
 * @package Gambio\Core\Application\Bootstrapper
 */
class LoadServerInformation implements Bootstrapper
{
    /**
     * @inheritDoc
     */
    public function boot(Application $application): void
    {
        $modRewriteWorking = $this->getServerValue('gambio_mod_rewrite_working');
        $htaccessVersion   = $this->getServerValue('gambio_htaccessVersion');
        
        $application->registerShared(ServerInformation::class)->addArgument($modRewriteWorking)->addArgument(
            $htaccessVersion
        );
    }
    
    
    /**
     * Returns the server value of given key, if available and null otherwise.
     *
     * @param string $key
     *
     * @return string|null
     */
    private function getServerValue(string $key): ?string
    {
        return $_SERVER[$key] ?? null;
    }
}