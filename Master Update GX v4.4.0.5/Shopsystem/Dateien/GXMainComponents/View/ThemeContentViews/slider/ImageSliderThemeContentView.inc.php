<?php

/* --------------------------------------------------------------
  ImageSliderThemeContentView.inc.php 2018-11-13
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2017 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------
 */

class ImageSliderThemeContentView extends ThemeContentView
{
    protected $slider_set_id;
    protected $language_id;
    
    
    /*
     * constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->set_content_template('image_slider.html');
    }
    
    
    public function prepare_data()
    {
        $this->build_html = false;
        
        // Set serialized slider JSON string.
        $t_coo_slider_read_service                     = StaticGXCoreLoader::getService('SliderRead');
        $slider                                        = $t_coo_slider_read_service->getSliderById(new IdType($this->slider_set_id));
        $t_coo_slider_json_serializer                  = MainFactory::create('SliderJsonSerializer');
        $t_json_serialized_slider                      = $t_coo_slider_json_serializer->serialize($slider);
        $this->content_array['json_serialized_slider'] = $t_json_serialized_slider;
        
        $t_json_placeholder_slide = [
            'baseUrl'    => HTTP_SERVER . DIR_WS_CATALOG . 'images/slider_images/',
            'languageId' => $_SESSION['languages_id']
        ];
        
        $this->content_array['json_placeholder_slide'] = json_encode($t_json_placeholder_slide);
        
        $this->build_html = true;
    }
    
    
    protected function set_validation_rules()
    {
        // GENERAL VALIDATION RULES
        $this->validation_rules_array['slider_set_id'] = ['type' => 'int'];
        $this->validation_rules_array['language_id']   = ['type' => 'int'];
    }
}
