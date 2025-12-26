<?php
/* --------------------------------------------------------------
   CatalogSelectWidgetAjaxController.inc.php 2017-11-30
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2017 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class CatalogSelectWidgetAjaxController
 *
 * This ajax controller is used by the catalog selector widget to fetch all products and categories in the shop.
 *
 * @category   System
 * @package    AdminHttpViewControllers
 * @extends    AdminHttpViewController
 */
class CatalogSelectWidgetAjaxController extends AdminHttpViewController
{
    /**
     * @var CategoryReadService
     */
    protected $categoryReadService;
    
    /**
     * @var ProductReadServiceInterface
     */
    protected $productReadService;
    
    /**
     * @var LanguageCode
     */
    protected $languageCode;
    
    /**
     * @var bool
     */
    protected $tooManyProducts;
    
    
    /**
     * Init
     */
    public function init()
    {
        $this->categoryReadService = StaticGXCoreLoader::getService('CategoryRead');
        $this->productReadService  = StaticGXCoreLoader::getService('ProductRead');
        
        $languageProvider   = MainFactory::create('LanguageProvider', StaticGXCoreLoader::getDatabaseQueryBuilder());
        $this->languageCode = $languageProvider->getCodeById(new IdType((int)$_SESSION['languages_id']));
        
        $sql    = 'SELECT count(*) AS `count` FROM `products_description` WHERE `language_id` = '
                  . (int)$_SESSION['languages_id'];
        $query  = xtc_db_query($sql);
        $result = xtc_db_fetch_array($query);
        if ($result !== false) {
            $this->tooManyProducts = $result['count'] > 10000;
        } else {
            $this->tooManyProducts = false;
        }
    }
    
    
    /**
     * Returns the categorie tree of the shop.
     *
     * @return bool|\JsonHttpControllerResponse
     * @throws \AuthenticationException
     */
    public function actionGetCategoriesTree()
    {
        if (!$this->_isAdmin()) {
            throw new AuthenticationException('No admin privileges. Please contact the administrator.');
        }
        
        $response = [
            'id'       => 0,
            'name'     => TEXT_TOP,
            'children' => $this->_getCategoriesTreeData($withProducts = false),
        ];
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * Returns the categorie tree of the shop including all products.
     *
     * @return bool|\JsonHttpControllerResponse
     * @throws \AuthenticationException
     */
    public function actionGetProductsTree()
    {
        if (!$this->_isAdmin()) {
            throw new AuthenticationException('No admin privileges. Please contact the administrator.');
        }
        
        $response = [
            'id'       => 0,
            'text'     => TEXT_TOP,
            'children' => $this->_getCategoriesTreeData($withProducts = true),
            'products' => $this->_getCategoryProducts(new IdType(0)),
        ];
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * Returns the optgroup HTML for the categorie tree of the shop including all products.
     *
     * This HTML should be used for a select dropdown.
     *
     * @return bool|\JsonHttpControllerResponse
     * @throws \AuthenticationException
     */
    public function actionGetProductsTreeAsOptgroups()
    {
        if (!$this->_isAdmin()) {
            throw new AuthenticationException('No admin privileges. Please contact the administrator.');
        }
        $response = $this->_generateOptgroupsHtml([
                                                      'id'       => 0,
                                                      'text'     => TEXT_TOP,
                                                      'children' => ($this->tooManyProducts) ? [] : $this->_getCategoriesTreeData($withProducts = true),
                                                      'products' => $this->_getCategoryProducts(($this->tooManyProducts) ? null : new IdType(0)),
                                                  ]);
        
        return MainFactory::create('JsonHttpControllerResponse', ['html' => '<option></option>' . $response]);
    }
    
    
    /**
     * Generates the html for the dropdown of the products tree.
     *
     * @return string HTML Code of a dropdown
     */
    protected function _generateOptgroupsHtml($data, $level = 1)
    {
        if ($level >= 5) {
            $level = 5;
        }
        
        $spacing = '';
        for ($i = 2; $i <= $level; $i++) {
            $spacing = $spacing . '&nbsp;&nbsp;&nbsp;&nbsp;';
        }
        
        $return = (($this->tooManyProducts) ? '' : '<optgroup label="' . $spacing . $data['text'] . '">');
        
        if ($data['products'] != null) {
            foreach ($data['products'] as $product) {
                $return .= '<option value="' . $product['id'] . '">' . $spacing . $product['text'] . '</option>';
            }
        }
        
        if ($data['children'] != null) {
            if (count($data['children'])) {
                $return .= '</optgroup>';
            }
            
            foreach ($data['children'] as $child) {
                $return .= $this->_generateOptgroupsHtml($child, $level + 1);
            }
        }
    
        if (!$this->tooManyProducts && ($level > 1 && !count($data['children']))) {
            $return .= '</optgroup>';
        }
        
        return $return;
    }
    
    
    /**
     * Check if the customer is the admin.
     *
     * @return bool Is the customer the admin?
     */
    protected function _isAdmin()
    {
        try {
            $this->validateCurrentAdminStatus();
            
            return true;
        } catch (LogicException $exception) {
            return false;
        }
    }
    
    
    /**
     * Collects the data for the categories tree.
     *
     * @param bool $withProducts
     * @param null $parentId
     *
     * @return array
     */
    protected function _getCategoriesTreeData($withProducts = false, $parentId = null)
    {
        $return     = [];
        $categories = $this->categoryReadService->getCategoryList($this->languageCode, $parentId);
        
        /**
         * @var CategoryListItem $product
         */
        foreach ($categories->getArray() as $category) {
            if ($withProducts) {
                $return[] = [
                    'id'       => $category->getCategoryId(),
                    'text'     => $category->getName(),
                    'children' => $this->_getCategoriesTreeData($withProducts, new IdType($category->getCategoryId())),
                    'products' => $this->_getCategoryProducts(new IdType($category->getCategoryId())),
                ];
            } else {
                $return[] = [
                    'id'       => $category->getCategoryId(),
                    'text'     => $category->getName(),
                    'children' => $this->_getCategoriesTreeData($withProducts, new IdType($category->getCategoryId())),
                ];
            }
        }
        
        return $return;
    }
    
    
    /**
     * Collects the data of all products of a category.
     *
     * @param null $categoryId
     *
     * @return array
     */
    protected function _getCategoryProducts($categoryId = null)
    {
        $return = [];
        
        // plain sql instead of product read service is used for performance reasons
        
        if ($categoryId !== null) {
            $sql = 'SELECT 
							pd.`products_id`,
							pd.`products_name` 
						FROM 
							`products_description` pd, 
							`products_to_categories` ptc 
						WHERE 
							pd.`products_id` = ptc.`products_id` AND
							pd.`language_id` = ' . (int)$_SESSION['languages_id'] . ' AND
					        ptc.`categories_id` = ' . (int)$categoryId->asInt() . '
					    ORDER BY
					        pd.`products_name` ASC';
        } else {
            $sql = 'SELECT `products_id`, `products_name` 
					FROM `products_description` 
					WHERE `language_id` = ' . (int)$_SESSION['languages_id'] . '
				    ORDER BY `products_name` ASC';
        }
        
        $result = xtc_db_query($sql);
        while ($row = xtc_db_fetch_array($result)) {
            $return[] = [
                'id'   => $row['products_id'],
                'text' => $row['products_name']
            ];
        }
        
        return $return;
    }
}