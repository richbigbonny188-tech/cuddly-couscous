<?php

/* --------------------------------------------------------------
   GambioUpdateSerializer.inc.php 2018-09-28
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2018 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

class GambioUpdateSerializer
{
	function serialize($target)
	{
		return serialize($target);
	}

	function deserialize($target)
	{
		return unserialize($target);
	}
}