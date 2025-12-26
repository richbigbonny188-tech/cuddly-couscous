<?php

/* --------------------------------------------------------------
   VPEWriteServiceInterface.inc.php 2017-07-24
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2017 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Interface VPEWriteServiceInterface
 *
 * @category   System
 * @package    VPE
 */
interface VPEWriteServiceInterface
{
    /**
     * Saves VPE entity in database.
     *
     * @param \VPEInterface $vpe VPE entity to be saved.
     *
     * @return $this|\VPEWriteServiceInterface Same instance for chained method calls.
     */
    public function save(VPEInterface $vpe);
    
    
    /**
     * Deletes VPE entity from database.
     *
     * @param \VPEInterface $vpe VPE entity to be deleted.
     *
     * @return $this|\VPEWriteServiceInterface Same instance for chained method calls.
     */
    public function delete(VPEInterface $vpe);
    
    
    /**
     * Creates VPE entity.
     *
     * @return VPE New VPE entity.
     */
    public function createVPE();
}