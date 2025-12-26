<?php
/*------------------------------------------------------------------------------
 OnGetSellingUnitVpeEventListener.php 2021-01-25
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\Attributes\SellingUnit\Database\Listener;

use Gambio\Shop\Attributes\SellingUnit\Database\Exceptions\AttributeDoesNotExistsException;
use Gambio\Shop\Attributes\SellingUnit\Database\Service\ReadServiceInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitVpeEventInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Vpe;

class OnGetSellingUnitVpeEventListener
{
    /**
     * @var ReadServiceInterface
     */
    private $service;
    
    
    /**
     * OnGetSellingUnitVpeEventListener constructor.
     *
     * @param ReadServiceInterface $service
     */
    public function __construct(ReadServiceInterface $service)
    {
        
        $this->service = $service;
    }
    
    
    /**
     * @param OnGetSellingUnitVpeEventInterface $event
     *
     * @return OnGetSellingUnitVpeEventInterface
     */
    public function __invoke(OnGetSellingUnitVpeEventInterface $event): OnGetSellingUnitVpeEventInterface
    {
        if ($event->product()->getVpeStatus()) {
            try {
                $attribute = $this->service->getVpeFor($event->id());
                if ($attribute && $attribute->vpeId() && $attribute->vpeValue()) {
                    $vpe = new Vpe($attribute->vpeId(), $attribute->vpeName(), $attribute->vpeValue());
                    $event->setVpe($vpe, 5000);
                }
            } catch (AttributeDoesNotExistsException $e) {
                //do nothing
            }
        }
        
        return $event;
    }
}
