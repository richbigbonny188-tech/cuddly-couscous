<?php
/*--------------------------------------------------------------------------------------------------
    CookieConfigurationList.php 2019-12-19
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2019 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

/**
 * Class CookieConfigurationList
 */
class CookieConfigurationList extends EditableCollection implements JsonSerializable
{
    /**
     * Get valid type.
     *
     * This method must be implemented in the child-collection classes.
     *
     * @return string
     */
    protected function _getValidType()
    {
        return CookieConfigurationInterface::class;
    }
    
    
    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->getArray();
    }
}