<?php
/* --------------------------------------------------------------
 SearchServiceProvider.php 2020-09-16
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Modules\App\Commands;

/**
 * Class SearchServiceProvider
 * @package Gambio\Core\Framework\Module\Commands
 */
class SearchServiceProvider
{
    /**
     * @var array
     */
    private $serviceProviders = [];
    
    
    /**
     * Adds a new service provider to the collection.
     *
     * @param string $providerFqn
     */
    public function addServiceProvider(string $providerFqn): void
    {
        if (class_exists($providerFqn)) {
            $this->serviceProviders[] = $providerFqn;
        }
    }
    
    
    /**
     * @return array
     */
    public function serviceProviderList(): array
    {
        return $this->serviceProviders;
    }
}