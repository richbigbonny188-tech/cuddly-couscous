<?php
/*--------------------------------------------------------------------
 OnImageCollectionCreateEventListener.php 2020-08-05
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Properties\SellingUnitImages\Database\Listener;

use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollection;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollectionInterface;
use Gambio\Shop\Properties\ProductModifiers\Database\ValueObjects\PropertyModifierIdentifier;
use Gambio\Shop\Properties\SellingUnitImages\Database\Exceptions\ImageListIsEmptyException;
use Gambio\Shop\Properties\SellingUnitImages\Database\Exceptions\PropertyDoesNotHaveAnImageListException;
use Gambio\Shop\Properties\SellingUnitImages\Database\Service\ReadServiceInterface;
use Gambio\Shop\SellingUnit\Database\Image\Events\OnImageCollectionCreateEventInterface;
use GmConfigurationServiceInterface;
use StaticGXCoreLoader;

/**
 * Class OnCollectionCreateEventListener
 * @package Gambio\Shop\Properties\SellingUnitImages\Database\Listener
 */
class OnImageCollectionCreateEventListener
{
    /**
     * @var ReadServiceInterface
     */
    protected $readService;
    /**
     * @var string
     */
    private $configuration;


    /**
     * OnMainImageCreateEventListener constructor.
     *
     * @param ReadServiceInterface $readService
     * @param $configuration
     */
    public function __construct(ReadServiceInterface $readService, string $configuration)
    {
        $this->readService = $readService;
        $this->configuration = $configuration;
    }
    
    
    /**
     * @param OnImageCollectionCreateEventInterface $event
     *
     * @return OnImageCollectionCreateEventInterface
     */
    public function __invoke(OnImageCollectionCreateEventInterface $event): OnImageCollectionCreateEventInterface
    {
        $selectedPropertyValues = $this->getPropertiesIds($event->id()->modifiers());
        
        if (count($selectedPropertyValues)) {
            
            try {
                $imageCollection = $this->readService->getImageListImages($event->id()->productId(),
                                                                          new ModifierIdentifierCollection($selectedPropertyValues),
                                                                          $event->id()->language());
                if($this->configuration === 'DISPLAY_ONLY_PROPERTY_IMAGES') {
                    $event->builder()->wipeData();
                    $event->builder()->withImages($imageCollection);
                    $event->stopPropagation();
                } else {
                    $event->builder()->withImages($imageCollection);
                }




            } catch (ImageListIsEmptyException | PropertyDoesNotHaveAnImageListException $exception) {
                unset($exception);
            }
        }
        
        return $event;
    }
    
    
    /**
     * @param ModifierIdentifierCollectionInterface $modifiers
     *
     * @return array
     */
    protected function getPropertiesIds(ModifierIdentifierCollectionInterface $modifiers): array
    {
        $selectedPropertyValues = [];
        foreach ($modifiers as $modifier) {
            
            if ($modifier instanceof PropertyModifierIdentifier) {
                
                $selectedPropertyValues[] = $modifier;
            }
        }
        
        return $selectedPropertyValues;
    }
}