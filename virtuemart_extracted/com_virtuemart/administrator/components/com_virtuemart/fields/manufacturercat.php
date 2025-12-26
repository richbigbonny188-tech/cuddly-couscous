<?php
defined('JPATH_BASE') or die;
/**
 * Supports a modal Manufacturer picker.
 * @author Max Milbers, Valerie Cartan Isaksen
 * @copyright Copyright (C) 20240VirtueMart Team - All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL 2, see COPYRIGHT.php
 */

jimport('joomla.form.formfield');

class JFormFieldManufacturercat extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @author      Valerie Cartan Isaksen
	 * @var		string
	 *
	 */
	var $type = 'manufacturercat';

	function getInput() {

		if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');
		VmConfig::loadConfig();
		$model = VmModel::getModel('Manufacturercategories');
		$mc = $model->getManufacturerCategories();

		$emptyOption = JHtml::_ ('select.option', '', vmText::_ ('COM_VIRTUEMART_LIST_EMPTY_OPTION'), 'virtuemart_manufacturercategories_id', 'mf_category_name');
		if(!empty($mc) and is_array($mc)){
			array_unshift ($mc, $emptyOption);
		} else {
			$mc = array($emptyOption);
		}

		return JHtml::_('select.genericlist', $mc, $this->name, 'class="form-select"', 'virtuemart_manufacturercategories_id', 'mf_category_name', $this->value, $this->id);
	}


}
