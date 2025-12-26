<?php
/*--------------------------------------------------------------------------------------------------
    OnGetSellingUnitVpeEventInterface.php 2021-01-25
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2021 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces;

use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\VpeBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Vpe;

interface OnGetSellingUnitVpeEventInterface extends BasicSellingUnitEventInterface
{
    
    /**
     * @param Vpe|null $vpe
     * @param int      $priority
     */
    public function setVpe(?Vpe $vpe, int $priority): void;
    
    
    /**
     * @return Vpe
     */
    public function vpe(): ?Vpe;
    
    
    /**
     *
     */
    public function stop(): void;
}
