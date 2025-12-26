<?php
/**
 *
 * Description
 *
 * @package    VirtueMart
 * @subpackage Config
 * @author RickG
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - Copyright (C) 2004 - 2022 Virtuemart Team. All rights reserved. VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_pricing_prices.php 11052 2024-09-06 10:17:55Z yourgeek $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$js = 'Virtuemart.showprices;';
vmJsApi::addJScript('show_prices', $js, true);

?>

<div class="uk-card uk-card-small uk-card-vm">
	<div class="uk-card-header">
		<div class="uk-card-title">
						<span class="md-color-cyan-600 uk-margin-small-right"
								uk-icon="icon: cog; ratio: 1.2"></span>
			<?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_PRICES'); ?>
		</div>
	</div>
	<div class="uk-card-body">
		<?php
		echo VmuikitHtml::row('checkbox', 'COM_VIRTUEMART_ADMIN_CFG_SHOW_PRICES', 'show_prices', VmConfig::get('show_prices', 1), 1, 0, 'id="show_prices"');
		echo VmuikitHtml::row('checkbox', 'COM_VIRTUEMART_ADMIN_CFG_SIMPLE_PRICES_DISPLAY', 'simple_prices_display', VmConfig::get('simple_prices_display', 0), 1, 0, 'id="simple_prices_display"');
		?>
		<?php
		$params = $this->config->_params;
		$showPricesLine = false;
		require('default_priceconfig.php');

		?>
	</div>
</div>
