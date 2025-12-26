<?php
/*--------------------------------------------------------------------------------------------------
    SellingUnitIdFactory.php 2020-08-04
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\SellingUnit\Unit\Factories;

use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\SellingUnit\Unit\Builders\SellingUnitIdWithDefaultValuesBuilder;
use Gambio\Shop\SellingUnit\Unit\Events\OnSellingUnitIdCreateEvent;
use Gambio\Shop\SellingUnit\Unit\Factories\Interfaces\SellingUnitIdFactoryInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use Psr\EventDispatcher\EventDispatcherInterface;

class SellingUnitIdFactory implements SellingUnitIdFactoryInterface
{
    
    /**
     * @var SellingUnitIdFactory
     */
    private static $instance;
    
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;
    
    
    /**
     * SellingUnitIdFactory constructor.
     *
     * @param EventDispatcherInterface|null $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher = null)
    {
        $this->dispatcher = $dispatcher ?? \LegacyDependencyContainer::getInstance()->get(EventDispatcherInterface::class);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createFromProductString(string $value, LanguageId $languageId): SellingUnitId
    {
        $event = new OnSellingUnitIdCreateEvent('product', $value, new SellingUnitIdWithDefaultValuesBuilder($this->dispatcher));
        $event->builder()->withLanguageId($languageId);
        /**
         * @var OnSellingUnitIdCreateEvent $event
         */
        $event = $this->dispatcher->dispatch($event);
        
        return $event->builder()->build();
    }
    
    
    /**
     * @inheritDoc
     */
    public function createFromInfoString(string $value, LanguageId $languageId): SellingUnitId
    {
        $event = new OnSellingUnitIdCreateEvent('info', $value, new SellingUnitIdWithDefaultValuesBuilder($this->dispatcher));
        $event->builder()->withLanguageId($languageId);
        /**
         * @var OnSellingUnitIdCreateEvent $event
         */
        $event = $this->dispatcher->dispatch($event);
        
        return $event->builder()->build();
    }
    
    
    /**
     * @inheritDoc
     */
    public function createFromCustom($type, $value, LanguageId $languageId): SellingUnitId
    {
        $event = new OnSellingUnitIdCreateEvent($type, $value, new SellingUnitIdWithDefaultValuesBuilder($this->dispatcher));
        $event->builder()->withLanguageId($languageId);
        /**
         * @var OnSellingUnitIdCreateEvent $event
         */
        $event = $this->dispatcher->dispatch($event);
        
        return $event->builder()->build();
    }
    
    
    /**
     * @codeCoverageIgnore LegacyDependencyContainer not testable in V4 test environment
     * 
     * @return SellingUnitIdFactory
     */
    public static function instance() {
        if(static::$instance === null){
            static::$instance = new static();
        }
        return static::$instance;
    }
}