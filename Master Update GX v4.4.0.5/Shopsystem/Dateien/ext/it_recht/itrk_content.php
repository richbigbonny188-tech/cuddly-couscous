<?php
/* --------------------------------------------------------------
   itrk_content.php 2020-12-21
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2018 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

if(!defined('_GM_VALID_CALL'))
{
	chdir('../../');
	require 'includes/application_top.php';
	$include_mode = false;
}
else
{
	$include_mode = true;
}

isset($itrk_file_type) or die('unable to determine file type');

$itrk_supported_languages = ['de', 'be', 'fr', 'nl', 'es', 'en', 'sv', 'da', 'it', 'pl'];
$fallback_language = 'de';

$itrk_language    = $_SESSION['language_code'];
$itrkFile         = __DIR__ . '/../../media/content/itrk_' . $itrk_file_type . '_' . $itrk_language . '.html';
$contentAvailable = file_exists($itrkFile) && is_readable($itrkFile);
if(!in_array($itrk_language, $itrk_supported_languages, true) || !$contentAvailable)
{
	$itrk_language = $fallback_language;
	$itrkFile         = __DIR__ . '/../../media/content/itrk_' . $itrk_file_type . '_' . $itrk_language . '.html';
	$contentAvailable = file_exists($itrkFile) && is_readable($itrkFile);
}

if(!$contentAvailable)
{
	echo 'IT-Recht Kanzlei: Rechtstext nicht verf&uuml;gbar / Legal information not available';
}
else
{
?>
	<?php if(!$include_mode): ?>
		<!DOCTYPE html>
		<html>
		<head>
			<title><?php echo $itrk_file_type ?></title>
			<style>
				body { font: 0.85em sans-serif; }
			</style>
		</head>
		<body>
	<?php endif ?>
	<?php include $itrkFile ?>
	<?php if(!$include_mode): ?>
		</body>
		</html>
	<?php endif ?>
<?php
}
