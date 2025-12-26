<?php
/*--------------------------------------------------------------------------------------------------
    WidgetAdapter.php 2020-05-06
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2016 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\StyleEdit\Adapters;

use Gambio\StyleEdit\Adapters\Interfaces\WidgetAdapterInterface;
use Gambio\StyleEdit\Core\Widgets\Abstractions\AbstractWidget;

class WidgetAdapter implements WidgetAdapterInterface
{

    /**
     * @param string $widgetName
     * @param \stdClass $json
     * @return AbstractWidget
     */
    public function createWidget(string $widgetName, \stdClass $json): AbstractWidget
    {
        return $widgetName::createFromJsonObject($json);
    }
}