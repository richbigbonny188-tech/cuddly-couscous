<?php
/* --------------------------------------------------------------
  ProductModifierDisplayTypeFactoryInterface.php 2020-10-07
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

/**
 * Interface ProductModifierDisplayTypeFactoryInterface
 */
interface ProductModifierDisplayTypeFactoryInterface
{
    /**
     * @return ProductModifierDisplayTypeCollection
     */
    public function createCollection(): ProductModifierDisplayTypeCollection;
    
    
    /**
     * @param string      $elementName
     * @param string|null $selectedDisplayType
     *
     * @return ThemeContentView
     */
    public function createContentView(string $elementName, string $selectedDisplayType = null): ThemeContentView;
}