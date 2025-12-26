<?php
/*--------------------------------------------------------------------
 OnPresentSellingUnitIdEventListener.php 2020-11-30
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Properties\Representation\Id\Listener;

use Gambio\Shop\Properties\Database\Services\Interfaces\PropertiesReaderServiceInterface;
use Gambio\Shop\Properties\Properties\Entities\Combination;
use Gambio\Shop\Properties\Representation\ValueObjects\PropertyPresentationId;
use Gambio\Shop\SellingUnit\Presentation\Events\OnPresentSellingUnitIdEvent;

/**
 * Class OnPresentSellingUnitIdEventListener
 * @package Gambio\Shop\Properties\Representation\Id\Listener
 */
class OnPresentSellingUnitIdEventListener
{
    /**
     * @var PropertiesReaderServiceInterface
     */
    protected $readerService;
    
    
    /**
     * OnPresentSellingUnitIdEventListener constructor.
     *
     * @param PropertiesReaderServiceInterface $readerService
     */
    public function __construct(PropertiesReaderServiceInterface $readerService)
    {
        $this->readerService = $readerService;
    }
    
    
    /**
     * @param OnPresentSellingUnitIdEvent $event
     */
    public function __invoke(OnPresentSellingUnitIdEvent $event)
    {
        $combination = $this->readerService->getCombinationFor($event->sellingUnitId());
        
        if ($combination) {
            $presentationId = $this->createPropertyPresentationId($combination);
            $event->presentationIdCollection()[] = $presentationId;
        }
    }
    
    
    /**
     * @inheritDoc
     */
    protected function createPropertyPresentationId(Combination $combination): PropertyPresentationId
    {
        return new PropertyPresentationId($combination->id()->value());
    }
}