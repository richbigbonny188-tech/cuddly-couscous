<?php
/* --------------------------------------------------------------
 TemplateEngine.php 2020-09-03
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\TemplateEngine;

use Gambio\Core\TemplateEngine\Exceptions\RenderingFailedException;

/**
 * Interface TemplateEngine
 * @package Gambio\Core\TemplateEngine
 */
interface TemplateEngine
{
    /**
     * Renders the template.
     *
     * @param string $templatePath
     * @param array  $data
     *
     * @return string
     * @throws RenderingFailedException
     */
    public function render(string $templatePath, array $data = []): string;
}