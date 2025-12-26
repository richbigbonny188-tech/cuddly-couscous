<?php
/* --------------------------------------------------------------
   categories.php 2020-01-30
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

$categoryId = $this->category_id;
if (empty($this->category_id) && is_string($this->c_path)) {
    $cPathArray = explode('_', $this->c_path);
    $categoryId = array_pop($cPathArray);
}

if (gm_get_conf('SHOW_SUBCATEGORIES') === 'true') {
    
    /** @var CategoriesMenuBoxThemeContentView $categoriesBox */
    $categoriesBox = MainFactory::create_object('CategoriesMenuBoxThemeContentView');
    $categoriesBox->set_categories_left_template();
    $categoriesBox->set_tree_depth(0);
    $categoriesBox->setCategoryId($categoryId);
    $readService = MainFactory::create(CategoryServiceFactory::class,
                                       StaticGXCoreLoader::getDatabaseQueryBuilder(),
                                       MainFactory::create(EnvCategoryServiceSettings::class),
                                       MainFactory::create('GMSEOBoost'))->createCategoryReadService();
    
    if ($categoryId > 0) {
        $category = $readService->getCategoryById(new IdType((int)$categoryId));
        
        if ($category->getParentId() === 0 && gm_get_conf('CAT_MENU_CLASSIC') == "false") {
            $this->set_content_data('HAS_SUBCATEGORIES', false);
        } elseif ($readService->getCategoryIdsTree(new IdType((int)$categoryId))->count() > 1) {
            $this->set_content_data('HAS_SUBCATEGORIES', true);
            $categoriesBox->setCurrentCategoryId($categoryId);
        } else {
            // When the current category does not have any subcategories, the subcategories of the parent category are displayed
            $categories = $readService->getCategoryList(new LanguageCode(new StringType($_SESSION['language_code'])),
                                                        new IdType($categoryId));
            
            $categoriesBox->setCurrentCategoryId($category->getParentId());
            $this->set_content_data('HAS_SUBCATEGORIES', $categories->count() > 0 || gm_get_conf('SHOW_SUBCATEGORIES_PARENT') === "true");
        }
    } else {
        $this->set_content_data('HAS_SUBCATEGORIES', false);
    }
    
    $categoriesBox->setCPath($this->c_path);
    $categoriesBoxHtml = $categoriesBox->get_html();
    
    $boxPosition = $GLOBALS['coo_template_control']->get_menubox_position('categories');
    $this->set_content_data($boxPosition, $categoriesBoxHtml);
} elseif (gm_get_conf('CAT_MENU_LEFT') === 'true') {
    /** @var CategoriesMenuBoxThemeContentView $categoriesBox */
    $categoriesBox = MainFactory::create_object('CategoriesMenuBoxThemeContentView');
    $categoriesBox->set_categories_template();
    $categoriesBox->set_tree_depth(gm_get_conf('CATEGORY_UNFOLD_DEFAULT_LEVEL'));
    $categoriesBox->setCategoryId($categoryId);
    $categoriesBox->setCurrentCategoryId(0);
    $categoriesBox->setCPath($this->c_path);
    $categoriesBox->setUnfoldLevel(gm_get_conf('CATEGORY_UNFOLD_LEVEL'));
    
    $readService = MainFactory::create(CategoryServiceFactory::class,
                                       StaticGXCoreLoader::getDatabaseQueryBuilder(),
                                       MainFactory::create(EnvCategoryServiceSettings::class),
                                       MainFactory::create('GMSEOBoost'))->createCategoryReadService();
    if ($readService->getCategoryIdsTree(new IdType((int)$categoryId))->count() > 1) {
        $this->set_content_data('HAS_SUBCATEGORIES', true);
    } else {
        $this->set_content_data('HAS_SUBCATEGORIES', false);
    }
    
    if (gm_get_conf('CATEGORY_ACCORDION_EFFECT') === 'true') {
        $categoriesBox->activateAccordionEffect();
    } else {
        $categoriesBox->deactivateAccordionEffect();
    }
    
    if (gm_get_conf('CATEGORY_DISPLAY_SHOW_ALL_LINK') === 'true') {
        $categoriesBox->activateDisplayShowAllLink();
    } else {
        $categoriesBox->deactivateDisplayShowAllLink();
    }
    
    $categoriesBoxHtml = $categoriesBox->get_html();
    
    $boxPosition = $GLOBALS['coo_template_control']->get_menubox_position('categories');
    $this->set_content_data($boxPosition, $categoriesBoxHtml);
}


    

