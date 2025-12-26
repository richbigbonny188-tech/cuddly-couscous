<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_languages
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

?>
<div class="mod-languages">
    <p class="visually-hidden" id="language_picker_des_<?php echo $module->id; ?>"><?php echo Text::_('MOD_LANGUAGES_DESC'); ?></p>

<?php if ($headerText) : ?>
    <div class="mod-languages__pretext pretext"><p><?php echo $headerText; ?></p></div>
<?php endif; ?>

<?php if ($params->get('dropdown', 0)) : ?>
    <?php HTMLHelper::_('bootstrap.dropdown', '.dropdown-toggle'); ?>
    <div class="mod-languages__select btn-group">
        <?php foreach ($list as $language) : ?>
            <?php if ($language->active) : ?>
                <button id="language_btn_<?php echo $module->id; ?>" type="button" data-bs-toggle="dropdown" class="btn btn-link btn-sm dropdown-toggle p-0" aria-haspopup="listbox" aria-labelledby="language_picker_des_<?php echo $module->id; ?> language_btn_<?php echo $module->id; ?>" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-globe2" viewBox="0 0 16 16">
                       <path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m7.5-6.923c-.67.204-1.335.82-1.887 1.855q-.215.403-.395.872c.705.157 1.472.257 2.282.287zM4.249 3.539q.214-.577.481-1.078a7 7 0 0 1 .597-.933A7 7 0 0 0 3.051 3.05q.544.277 1.198.49zM3.509 7.5c.036-1.07.188-2.087.436-3.008a9 9 0 0 1-1.565-.667A6.96 6.96 0 0 0 1.018 7.5zm1.4-2.741a12.3 12.3 0 0 0-.4 2.741H7.5V5.091c-.91-.03-1.783-.145-2.591-.332M8.5 5.09V7.5h2.99a12.3 12.3 0 0 0-.399-2.741c-.808.187-1.681.301-2.591.332zM4.51 8.5c.035.987.176 1.914.399 2.741A13.6 13.6 0 0 1 7.5 10.91V8.5zm3.99 0v2.409c.91.03 1.783.145 2.591.332.223-.827.364-1.754.4-2.741zm-3.282 3.696q.18.469.395.872c.552 1.035 1.218 1.65 1.887 1.855V11.91c-.81.03-1.577.13-2.282.287zm.11 2.276a7 7 0 0 1-.598-.933 9 9 0 0 1-.481-1.079 8.4 8.4 0 0 0-1.198.49 7 7 0 0 0 2.276 1.522zm-1.383-2.964A13.4 13.4 0 0 1 3.508 8.5h-2.49a6.96 6.96 0 0 0 1.362 3.675c.47-.258.995-.482 1.565-.667m6.728 2.964a7 7 0 0 0 2.275-1.521 8.4 8.4 0 0 0-1.197-.49 9 9 0 0 1-.481 1.078 7 7 0 0 1-.597.933M8.5 11.909v3.014c.67-.204 1.335-.82 1.887-1.855q.216-.403.395-.872A12.6 12.6 0 0 0 8.5 11.91zm3.555-.401c.57.185 1.095.409 1.565.667A6.96 6.96 0 0 0 14.982 8.5h-2.49a13.4 13.4 0 0 1-.437 3.008M14.982 7.5a6.96 6.96 0 0 0-1.362-3.675c-.47.258-.995.482-1.565.667.248.92.4 1.938.437 3.008zM11.27 2.461q.266.502.482 1.078a8.4 8.4 0 0 0 1.196-.49 7 7 0 0 0-2.275-1.52c.218.283.418.597.597.932m-.488 1.343a8 8 0 0 0-.395-.872C9.835 1.897 9.17 1.282 8.5 1.077V4.09c.81-.03 1.577-.13 2.282-.287z"/>
                    </svg>
                    <?php echo $params->get('full_name', 1) ? $language->title_native : strtoupper($language->sef); ?>
                </button>
            <?php endif; ?>
        <?php endforeach; ?>
        <ul aria-labelledby="language_picker_des_<?php echo $module->id; ?>" class="lang-block dropdown-menu dropdown-menu-end">

        <?php foreach ($list as $language) : ?>
            <?php
                $lbl = '';
            if ($params->get('full_name') === 0) {
                $lbl = 'aria-label="' . $language->title_native . '"';
            }
            ?>
            <?php if (!$language->active) : ?>
                <li>
                    <a class="dropdown-item" <?php echo $lbl; ?> href="<?php echo htmlspecialchars_decode(htmlspecialchars($language->link, ENT_QUOTES, 'UTF-8'), ENT_NOQUOTES); ?>">
                        <?php if ($params->get('dropdownimage', 1) && ($language->image)) : ?>
                            <?php echo HTMLHelper::_('image', 'mod_languages/' . $language->image . '.gif', $params->get('full_name') ? '' : $language->title_native, null, true); ?>
                        <?php endif; ?>
                        <?php echo $params->get('full_name', 1) ? $language->title_native : strtoupper($language->sef); ?>
                    </a>
                </li>
            <?php elseif ($params->get('show_active', 1)) : ?>
                <?php $base = Uri::getInstance(); ?>
                <li class="lang-active">
                    <a class="dropdown-item" aria-current="true" <?php echo $lbl; ?> href="<?php echo htmlspecialchars_decode(htmlspecialchars($base, ENT_QUOTES, 'UTF-8'), ENT_NOQUOTES); ?>">
                        <?php if ($params->get('dropdownimage', 1) && ($language->image)) : ?>
                            <?php echo HTMLHelper::_('image', 'mod_languages/' . $language->image . '.gif', $params->get('full_name') ? '' : $language->title_native, null, true); ?>
                        <?php endif; ?>
                        <?php echo $params->get('full_name', 1) ? $language->title_native : strtoupper($language->sef); ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
        </ul>
    </div>
<?php else : ?>
    <ul aria-labelledby="language_picker_des_<?php echo $module->id; ?>" class="mod-languages__list <?php echo $params->get('inline', 1) ? 'lang-inline' : 'lang-block'; ?>">

    <?php foreach ($list as $language) : ?>
        <?php
            $lbl = '';
        if ((($params->get('full_name') === 0) && ($params->get('image') === 0)) || (!$language->image)) {
            $lbl = 'aria-label="' . $language->title_native . '"';
        }
        ?>
        <?php if (!$language->active) : ?>
            <li>
                <a <?php echo $lbl; ?> href="<?php echo htmlspecialchars_decode(htmlspecialchars($language->link, ENT_QUOTES, 'UTF-8'), ENT_NOQUOTES); ?>">
                    <?php if ($params->get('image', 1)) : ?>
                        <?php if ($language->image) : ?>
                            <?php echo HTMLHelper::_('image', 'mod_languages/' . $language->image . '.gif', $language->title_native, ['title' => $language->title_native], true); ?>
                        <?php else : ?>
                            <span class="label" title="<?php echo $language->title_native; ?>"><?php echo strtoupper($language->sef); ?></span>
                        <?php endif; ?>
                    <?php else : ?>
                        <?php echo $params->get('full_name', 1) ? $language->title_native : strtoupper($language->sef); ?>
                    <?php endif; ?>
                </a>
            </li>
        <?php elseif ($params->get('show_active', 1)) : ?>
            <?php $base = Uri::getInstance(); ?>
            <li class="lang-active">
                <a aria-current="true" <?php echo $lbl; ?> href="<?php echo htmlspecialchars_decode(htmlspecialchars($base, ENT_QUOTES, 'UTF-8'), ENT_NOQUOTES); ?>">
                    <?php if ($params->get('image', 1)) : ?>
                        <?php if ($language->image) : ?>
                            <?php echo HTMLHelper::_('image', 'mod_languages/' . $language->image . '.gif', $language->title_native, ['title' => $language->title_native], true); ?>
                        <?php else : ?>
                            <span class="badge bg-secondary" title="<?php echo $language->title_native; ?>"><?php echo strtoupper($language->sef); ?></span>
                        <?php endif; ?>
                    <?php else : ?>
                        <?php echo $params->get('full_name', 1) ? $language->title_native : strtoupper($language->sef); ?>
                    <?php endif; ?>
                </a>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if ($footerText) : ?>
    <div class="mod-languages__posttext posttext"><p><?php echo $footerText; ?></p></div>
<?php endif; ?>
</div>
