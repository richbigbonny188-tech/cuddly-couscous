<?php
/* --------------------------------------------------------------
   CategoriesIndex.inc.php 2020-12-17
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------*/

/**
 * Class CategoriesIndex
 * 
 * Use only PHP 5.2 syntax to remain compatibility to the gambio updater
 */
class CategoriesIndex
{
	protected $queryValuesPart          = '';
	protected $queryValuesPartMaxLength = 10000;
	protected $categoryStatus;
	protected $categoryParentIds;
	
	
	public function build_categories_index($p_productsId = null)
	{
		$wherePart = '';
		if($p_productsId !== null)
		{
			$wherePart = ' WHERE products_id = ' . (int)$p_productsId . ' ';
		}
		
		$productId   = null;
		$categoryIds = array();
		
		$query = 'SELECT categories_id, products_id FROM products_to_categories ' . $wherePart . 'ORDER BY products_id';
		
		$result = xtc_db_query($query, 'db_link', false, false);
		while($row = xtc_db_fetch_array($result))
		{
			if($productId === null || $productId === $row['products_id'])
			{
				$categoryIds[] = $row['categories_id'];
				$productId     = $row['products_id'];
				continue;
			}
			
			$this->_add_query_values_part($productId, $categoryIds);
			$this->_write_categories_index();
			
			$productId   = $row['products_id'];
			$categoryIds = array($row['categories_id']);
		}
		
		if($productId !== null)
		{
			$this->_add_query_values_part($productId, $categoryIds);
			$this->_write_categories_index(true);
		}
	}
	
	
	public function get_categories_parents_array($p_categoryId)
	{
		$outputArray = array();
		
		if($p_categoryId === '0' || $p_categoryId === 0)
		{
			# categories_id is root and has no parents. return empty array.
			return $outputArray;
		}
		
		# get category's status and parent_id
		$category = $this->getCategoryStatusAndParentId($p_categoryId);
		
		if($category === null || $category['categories_status'] === '0')
		{
			# cancel recursion with false on inactive category
			return false;
		}
		
		$parentId      = $category['parent_id'];
		$outputArray[] = $parentId;
		
		if($parentId !== '0')
		{
			# get more parents, if category is not root
			$parentIds = $this->get_categories_parents_array($parentId);
			if($parentIds === false)
			{
				# cancel recursion with false on inactive category
				return false;
			}
			
			if(count($parentIds))
			{
				# merge category's parent tree to categories_id
				foreach($parentIds as $parentId) 
				{
					$outputArray[] = $parentId;
				}
			}
		}
		
		return $outputArray;
	}
	
	
	protected function _add_query_values_part($p_productsId, array $p_categoryIds)
	{
		$categoryIds = array();
		
		foreach($p_categoryIds as $categoryId)
		{
			$t_parent_id_array = $this->get_categories_parents_array($categoryId);
			
			if($t_parent_id_array !== false)
			{
				$categoryIds[] = $categoryId;
				
				if(count($t_parent_id_array))
				{
					foreach($t_parent_id_array as $parentId) 
					{
						$categoryIds[] = $parentId;
					}
				}
			}
		}
		
		if(count($categoryIds) > 1)
		{
			sort($categoryIds); # sort array for cleaning
			$categoryIds = array_unique($categoryIds); # delete doubled categories_ids
		}
		
		# build index string
		$categoriesIndex = '';
		foreach($categoryIds as $categoryId)
		{
			$categoriesIndex .= '-' . $categoryId . '-';
		}
		
		$this->queryValuesPart .= '(' . $p_productsId . ',"' . $categoriesIndex . '"),';
	}
	
	
	protected function _write_categories_index($forceWrite = false)
	{
		if($this->queryValuesPart !== ''
		   && ($forceWrite
		       || strlen($this->queryValuesPart) > $this->queryValuesPartMaxLength)
		)
		{
			$query = 'REPLACE INTO `categories_index` 
							(`products_id`, `categories_index`)
						VALUES
							' . substr($this->queryValuesPart, 0, -1);
			
			# save built index
			xtc_db_query($query, 'db_link', false, false);
			
			$this->queryValuesPart = '';
		}
	}
	
	
	protected function getCategoryStatusAndParentId($categoryId)
	{
		if($this->categoryParentIds === null)
		{
			$this->categoryStatus    = array();
			$this->categoryParentIds = array();
			
			$query  = 'SELECT categories_id, categories_status, parent_id FROM categories';
			$result = xtc_db_query($query, 'db_link', false, false);
			
			while($row = xtc_db_fetch_array($result))
			{
				$this->categoryStatus[$row['categories_id']]    = $row['categories_status'];
				$this->categoryParentIds[$row['categories_id']] = $row['parent_id'];
			}
		}
		
		if(isset($this->categoryParentIds[$categoryId]))
		{
			return [
				'categories_status' => $this->categoryStatus[$categoryId],
				'parent_id'         => $this->categoryParentIds[$categoryId]
			];
		}
		
		return null;
	}
}