<?php
/* --------------------------------------------------------------
  ContentZoneAlreadyDefinedException.php 2019-07-24
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2019 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

/**
 * Class ContentZoneAlreadyDefinedException
 */
class ContentZoneAlreadyDefinedException extends Exception
{
    /**
     * ContentZoneAlreadyDefinedException constructor.
     *
     * @param string                   $contentZoneId
     * @param Smarty_Internal_Template $template
     * @param Throwable|null           $previous
     */
    public function __construct(string $contentZoneId, Smarty_Internal_Template $template, Throwable $previous = null)
    {
        $message = 'A ContentZone with the id (' . $contentZoneId . ') is defined multiple times.' . PHP_EOL;
        $message .= $template->template_resource . PHP_EOL;
        
        parent::__construct($message, 0, $previous);
    }
}