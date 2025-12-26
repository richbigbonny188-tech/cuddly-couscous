<?php

/**
 * @package     Joomla.Site
 * @subpackage  Templates.vmbasic
 *
 * @copyright   (C) 2024 Spiros Petrakis
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\Document\HtmlDocument $this */

$app   = Factory::getApplication();
$input = $app->getInput();
$wa    = $this->getWebAssetManager();

// Set responsive meta
$this->setMetaData('viewport', 'width=device-width, initial-scale=1');

// Load Bootstrap tooltip
\Joomla\CMS\HTML\HTMLHelper::_('bootstrap.tooltip');

// Load required css
$wa->useStyle('bootstrap5');
$wa->useStyle('vmbasic');
$wa->useStyle('fontawesome');

if ($this->params->get('customcss', true)) {
	$wa->useStyle('vmbasic.user');
}

if (!empty($this->params->get('bodyfontfamily')))
{
	$wa->addInlineStyle('
		body {
		' . $this->params->get('bodyfontfamily') . '
		}
	');
}

if (!empty($this->params->get('headingsfontfamily')))
{
	$wa->addInlineStyle('
		h1, .h1, h2, .h2, h3, .h3, h4, h5, h6 {
		' . $this->params->get('headingsfontfamily') . '
		}
	');
}


// Load required scripts
$wa->useScript('vmbasic');

// Detecting Active Variables
$option    = $input->getCmd('option', '');
$view      = $input->getCmd('view', '');
$layout    = $input->getCmd('layout', '');
$task      = $input->getCmd('task', '');
$itemid    = $input->getCmd('Itemid', '');
$sitename  = htmlspecialchars($app->get('sitename'), ENT_QUOTES, 'UTF-8');
$menu      = $app->getMenu()->getActive();
$pageclass = $menu !== null ? $menu->getParams()->get('pageclass_sfx', '') : '';

?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="metas" />
	<?php echo $this->params->get('gfonts'); ?>
	<jdoc:include type="styles" />
	<jdoc:include type="scripts" />
</head>
<body class="<?php echo $this->direction === 'rtl' ? 'rtl' : ''; ?>">
	<jdoc:include type="message" />
	<jdoc:include type="component" />
</body>
</html>
