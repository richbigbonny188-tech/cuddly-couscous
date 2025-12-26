<?php
/* --------------------------------------------------------------
   ContentDeleteServiceInterface.inc.php 2019-08-01
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2019 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Interface ContentDeleteServiceInterface
 */
interface ContentDeleteServiceInterface
{
    /**
     * deletes the content data in database by id.
     *
     * @param $contentGroupId
     *
     * @return self ContentDeleteServiceInterface Same instance for chained method calls.
     */
    public function deleteById($contentGroupId);
}