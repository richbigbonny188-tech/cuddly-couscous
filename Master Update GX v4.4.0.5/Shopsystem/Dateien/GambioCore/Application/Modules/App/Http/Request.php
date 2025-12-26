<?php
/* --------------------------------------------------------------
 Request.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Modules\App\Http;

use Gambio\Core\Application\Modules\Http\Request as HttpRequest;
use Slim\Http\ServerRequest as SlimRequest;

/**
 * Class Request
 * @package Gambio\Core\Framework\Module\Http
 */
class Request implements HttpRequest
{
    /**
     * @var SlimRequest
     */
    private $internal;
    
    
    /**
     * Request constructor.
     *
     * @param SlimRequest $internal
     */
    public function __construct(SlimRequest $internal)
    {
        $this->internal = $internal;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getParsedBody()
    {
        return $this->internal->getParsedBody();
    }
    
    
    /**
     * @inheritDoc
     */
    public function getParsedBodyParam(string $key, $default = null)
    {
        return $this->internal->getParsedBodyParam($key, $default);
    }
    
    
    /**
     * @inheritDoc
     */
    public function getAttribute($name, $default = null)
    {
        return $this->internal->getAttribute($name, $default);
    }
}