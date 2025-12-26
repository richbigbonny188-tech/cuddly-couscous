<?php
/**
 * ProductModifierDisplayTypeFactory.php 2020-10-07
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2020 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

/**
 * Class ProductModifierDisplayTypeFactory
 */
class ProductModifierDisplayTypeFactory implements ProductModifierDisplayTypeFactoryInterface
{
    
    protected $displayTypes = [
        ProductModifierDisplayTypeDropdown::class,
        ProductModifierDisplayTypeImage::class,
        ProductModifierDisplayTypeRadio::class,
        ProductModifierDisplayTypeText::class,
        ProductModifierDisplayTypeBoxedText::class,
    ];
    
    
    /**
     * @inheritDoc
     */
    public function createCollection(): ProductModifierDisplayTypeCollection
    {
        $valueObjects = [];
    
        foreach ($this->displayTypes as $class) {
            
            $valueObjects[] = $this->createDisplayType($class);
        }
        
        return new ProductModifierDisplayTypeCollection($valueObjects);
    }
    
    
    /**
     * @param string $className
     *
     * @return AbstractProductModifierDisplayType
     */
    protected function createDisplayType(string $className): AbstractProductModifierDisplayType
    {
        return $className::create();
    }
    
    
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function createContentView(string $elementName, string $selectedDisplayType = null): ThemeContentView
    {
        return new ProductModifierDisplayTypeThemeContentView($elementName, $this->createCollection(), $selectedDisplayType);
    }
}