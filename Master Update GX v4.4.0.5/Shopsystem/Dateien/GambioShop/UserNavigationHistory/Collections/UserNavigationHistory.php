<?php
/*--------------------------------------------------------------
   UserNavigationHistory.php 2021-04-06
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\UserNavigationHistory\Collections;

use Gambio\Shop\UserNavigationHistory\Entities\HistoryEntry;

/**
 * Class UserNavigationHistory
 * @package Gambio\Shop\UserNavigationHistory\Collections
 */
class UserNavigationHistory extends AbstractCollection
{
    protected const LENGTH = 50;
    
    /**
     * @inheritDoc
     */
    protected function isValid($value): bool
    {
        return $value instanceof HistoryEntry;
    }
    
    
    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        if ($offset === null) {
            
            $this->addEntry($value);
            return;
        }
        
        parent::offsetSet($offset, $value);
    }
    
    
    /**
     * @inheritDoc
     */
    public function current(): HistoryEntry
    {
        return $this->currentValue();
    }
    
    
    /**
     * @param HistoryEntry $entry
     */
    public function addEntry(HistoryEntry $entry): void
    {
        $oldValues    = array_splice($this->values, 0, static::LENGTH);
        $newValue     = array_merge([$entry], $oldValues);
        $this->values = $newValue;
    }
}