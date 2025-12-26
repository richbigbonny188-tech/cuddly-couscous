<?php
/* --------------------------------------------------------------
   AbstractEventRaisingEntity.php 2020-08-24
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\Event\Abstracts;

use Gambio\Core\Event\EventRaisingEntity;

/**
 * Class AbstractEventRaisingEntity
 *
 * @package Gambio\Core\Event\Abstracts
 */
class AbstractEventRaisingEntity implements EventRaisingEntity
{
    /**
     * @var object[]
     */
    private $raisedEvents = [];
    
    
    /**
     * @return object[]
     */
    public function releaseEvents(): array
    {
        $raisedEvents       = $this->raisedEvents;
        $this->raisedEvents = [];
        
        return $raisedEvents;
    }
    
    
    /**
     * @param object $event
     */
    protected function raiseEvent(object $event): void
    {
        $this->raisedEvents[] = $event;
    }
}