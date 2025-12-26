<?php
/* --------------------------------------------------------------
  ProductModifierDisplayTypeThemeContentView.php 2020-10-07
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

/**
 * Class ProductModifierDisplayTypeThemeContentView
 * @codeCoverageIgnore
 */
class ProductModifierDisplayTypeThemeContentView extends ThemeContentView
{
    /**
     * @var string|null
     */
    protected $selectedDisplayType;
    
    /**
     * @var ProductModifierDisplayTypeCollection
     */
    protected $displayTypeCollection;
    
    /**
     * @var string
     */
    protected $elementName;
    
    
    /**
     * ProductModifierDisplayTypeContentView constructor.
     *
     * @param string                               $elementName
     * @param ProductModifierDisplayTypeCollection $displayTypeCollection
     *
     * @param string|null                          $selectedDisplayType
     *
     * @throws Exception
     */
    public function __construct(
        string $elementName,
        ProductModifierDisplayTypeCollection $displayTypeCollection,
        string $selectedDisplayType = null
    ) {
        parent::__construct(false, false);
        
        $this->elementName           = $elementName;
        $this->selectedDisplayType   = $selectedDisplayType;
        $this->displayTypeCollection = $displayTypeCollection;
        
        $this->set_template_dir(__DIR__ . DIRECTORY_SEPARATOR . 'Html');
        $this->set_content_template('DisplayTypeSelect.html');
        $this->set_content_data('elementName', $elementName);
        $this->set_content_data('selectedDisplayType', $this->selectedDisplayType);
        $this->set_content_data('displayTypeCollection', $this->displayTypeCollection);
    }
}