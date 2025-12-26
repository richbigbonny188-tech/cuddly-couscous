<?php
/*--------------------------------------------------------------------------------------------------
    WidgetAdapterInterface.php 2020-05-06
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2016 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\StyleEdit\Adapters\Interfaces;


use Gambio\StyleEdit\Core\Widgets\Abstractions\AbstractWidget;
use stdClass;

interface WidgetAdapterInterface
{
    /**
     * @param string $widgetName
     * @param stdClass $json
     * @return AbstractWidget
     */
    public function createWidget(string $widgetName, stdClass $json): AbstractWidget;
}