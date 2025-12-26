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
	<?php echo $this->params->get('headcustomjs') ?: ''; ?>
</head>
<body class="<?php echo $this->direction === 'rtl' ? 'rtl' : ''; ?>">
	<div class="container p-5 bg-light mt-5">
		<jdoc:include type="message" />
		<p><strong><?php echo Text::_('JERROR_LAYOUT_ERROR_HAS_OCCURRED_WHILE_PROCESSING_YOUR_REQUEST'); ?></strong></p>
		<p><?php echo Text::_('JERROR_LAYOUT_NOT_ABLE_TO_VISIT'); ?></p>

		<ul>
			<li><?php echo Text::_('JERROR_LAYOUT_AN_OUT_OF_DATE_BOOKMARK_FAVOURITE'); ?></li>
			<li><?php echo Text::_('JERROR_LAYOUT_MIS_TYPED_ADDRESS'); ?></li>
			<li><?php echo Text::_('JERROR_LAYOUT_SEARCH_ENGINE_OUT_OF_DATE_LISTING'); ?></li>
			<li><?php echo Text::_('JERROR_LAYOUT_YOU_HAVE_NO_ACCESS_TO_THIS_PAGE'); ?></li>
		</ul>

		<p><?php echo Text::_('JERROR_LAYOUT_GO_TO_THE_HOME_PAGE'); ?></p>
		<p><a href="<?php echo $this->baseurl; ?>/index.php" class="btn btn-primary"><span class="icon-home" aria-hidden="true"></span> <?php echo Text::_('JERROR_LAYOUT_HOME_PAGE'); ?></a></p>

		<hr>

		<p><?php echo Text::_('JERROR_LAYOUT_PLEASE_CONTACT_THE_SYSTEM_ADMINISTRATOR'); ?></p>

		<div class="alert alert-danger">
			<span class="badge bg-secondary"><?php echo $this->error->getCode(); ?></span> <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?>
		</div>

		<?php if ($this->debug) : ?>
		<div>
			<?php echo $this->renderBacktrace(); ?>
			<?php // Check if there are more Exceptions and render their data as well ?>
			<?php if ($this->error->getPrevious()) : ?>
				<?php $loop = true; ?>
				<?php // Reference $this->_error here and in the loop as setError() assigns errors to this property and we need this for the backtrace to work correctly ?>
				<?php // Make the first assignment to setError() outside the loop so the loop does not skip Exceptions ?>
				<?php $this->setError($this->_error->getPrevious()); ?>
				<?php while ($loop === true) : ?>
					<p><strong><?php echo Text::_('JERROR_LAYOUT_PREVIOUS_ERROR'); ?></strong></p>
					<p><?php echo htmlspecialchars($this->_error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></p>
					<?php echo $this->renderBacktrace(); ?>
					<?php $loop = $this->setError($this->_error->getPrevious()); ?>
				<?php endwhile; ?>
				<?php // Reset the main error object to the base error ?>
				<?php $this->setError($this->error); ?>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
	<?php echo $this->params->get('bodycustomjs') ?: ''; ?>
</body>
</html>