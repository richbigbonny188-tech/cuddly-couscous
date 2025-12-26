<?php
/*--------------------------------------------------------------
   AbstractValueModifier.php 2020-12-16
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\StyleEdit\Core\Options\Entities;

use Gambio\StyleEdit\Core\Options\Entities\OptionInterface;
use stdClass;

/**
 * Class AbstractValueModifier
 * @package Gambio\StyleEdit\Core\Components\ValueModifier
 */
abstract class AbstractValueModifier
{
    /**
     * @var OptionInterface
     */
    protected $option;
    
    
    /**
     * @param OptionInterface $option
     *
     * @return AbstractValueModifier
     */
    public function setOption(OptionInterface $option): AbstractValueModifier
    {
        $this->option = $option;
        
        return $this;
    }
    
    
    /**
     * @param stdClass $optionData
     * @return mixed
     */
    abstract protected function parseOptionData(stdClass $optionData);
    
    
    /**
     * @return mixed
     */
    public function modify()
    {
        return $this->parseOptionData($this->option->jsonSerialize());
    }
}