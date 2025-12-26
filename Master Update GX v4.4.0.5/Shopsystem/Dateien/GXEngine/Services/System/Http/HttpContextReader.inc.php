<?php
/* --------------------------------------------------------------
   HttpContextReader.inc.php 2021-02-24
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

MainFactory::load_class('HttpContextReaderInterface');

/**
 * Class HttpContextReader
 *
 * @category   System
 * @package    Http
 * @implements HttpContextReaderInterface
 */
class HttpContextReader implements HttpContextReaderInterface
{
    /**
     * Returns the controller name for current http request context.
     *
     * @param HttpContextInterface $httpContext Object which holds information about the current http context.
     *
     * @return string Name of controller for the current http context.
     */
    public function getControllerName(HttpContextInterface $httpContext)
    {
        $doValue      = (string)$httpContext->getGetItem('do');
        $doPartsArray = explode('/', $doValue);
        
        return preg_split('/\W/', $doPartsArray[0])[0];
    }
    
    
    /**
     * Returns the name of the action method for the current http context.
     *
     * @param \HttpContextInterface $httpContext Object which holds information about the current http context.
     *
     * @return string Name of action method for the current http context.
     */
    public function getActionName(HttpContextInterface $httpContext)
    {
        $doValue      = (string)$httpContext->getGetItem('do');
        $doPartsArray = explode('/', $doValue);
        if (count($doPartsArray) < 2) {
            return '';
        }
        
        return preg_split('/\W/', $doPartsArray[1])[0];
    }
    
    
    /**
     * Returns an array which represents the global $_GET variable of the current http context.
     *
     * @param HttpContextInterface $httpContext Object which holds information about the current http context.
     *
     * @return array Which holds information equal to the global $_GET variable in an object oriented layer.
     */
    public function getQueryParameters(HttpContextInterface $httpContext)
    {
        return $httpContext->getGetArray();
    }
    
    
    /**
     * Returns an array which represents the global $_POST variable of the current http context.
     *
     * @param HttpContextInterface $httpContext Object which holds information about the current http context.
     *
     * @return array Which holds information equal to the global $_POST variable in an object oriented layer.
     */
    public function getPostData(HttpContextInterface $httpContext)
    {
        return $httpContext->getPostArray();
    }
    
    
    /**
     * Returns an array which represents the global $_SERVER variable of the current http context.
     *
     * @param HttpContextInterface $httpContext Object which holds information about the current http context.
     *
     * @return array Array which hold information equal to the global $_SERVER variable in an object oriented layer.
     */
    public function getServerData(HttpContextInterface $httpContext)
    {
        return $httpContext->getServerArray();
    }
}