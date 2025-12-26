<?php
/* --------------------------------------------------------------
 Response.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Modules\App\Http;

use Gambio\Core\Application\Modules\Http\Response as HttpResponse;
use Slim\Http\Response as SlimResponse;

/**
 * Class Response
 * @package Gambio\Core\Framework\Module\Http
 */
class Response implements HttpResponse
{
    /**
     * @var SlimResponse
     */
    private $internal;
    
    
    /**
     * Response constructor.
     *
     * @param SlimResponse $internal
     */
    public function __construct(SlimResponse $internal)
    {
        $this->internal = $internal;
    }
    
    
    /**
     * Returns a slim response instance.
     *
     * @return SlimResponse
     */
    public function toSlimResponse(): SlimResponse
    {
        return $this->internal;
    }
    
    
    /**
     * @inheritDoc
     */
    public function write(string $data): HttpResponse
    {
        $this->internal = $this->internal->write($data);
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withHeader(string $name, $value): HttpResponse
    {
        $this->internal = $this->internal->withHeader($name, $value);
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withJson($data, ?int $status = null, int $options = 0, int $depth = 512): HttpResponse
    {
        $this->internal = $this->internal->withJson($data, $status, $options, $depth);
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withRedirect(string $url, ?int $status = null): HttpResponse
    {
        $this->internal = $this->internal->withRedirect($url, $status);
        
        return $this;
    }
}