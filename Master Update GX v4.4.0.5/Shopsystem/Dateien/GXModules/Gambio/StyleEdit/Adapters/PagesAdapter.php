<?php
/*--------------------------------------------------------------------------------------------------
    PagesAdapter.php 2020-07-16
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

namespace Gambio\StyleEdit\Adapters;

use Gambio\StyleEdit\Adapters\Interfaces\PagesAdapterInterface;
use \PagesNamespaceProvider;

class PagesAdapter implements PagesAdapterInterface
{
    /**
     * @var PagesNamespaceProvider
     */
    private $provider;
    
    
    /**
     * PagesAdapter constructor.
     *
     * @param PagesNamespaceProvider $provider
     */
    public function __construct(PagesNamespaceProvider $provider)
    {
        $this->provider = $provider;
    }
    
    
    /**
     * @return array|string[]
     */
    public function getPagesNamespaces(): array
    {
        return $this->provider->getPagesNamespaces();
    }
}