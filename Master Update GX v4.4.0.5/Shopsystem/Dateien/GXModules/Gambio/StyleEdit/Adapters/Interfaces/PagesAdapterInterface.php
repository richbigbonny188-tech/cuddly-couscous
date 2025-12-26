<?php
/*--------------------------------------------------------------------------------------------------
    PagesAdapterInterface.php 2020-07-16
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/
namespace Gambio\StyleEdit\Adapters\Interfaces;

interface PagesAdapterInterface
{
    /**
     * @return array
     */
    public function getPagesNamespaces(): array;
    
}