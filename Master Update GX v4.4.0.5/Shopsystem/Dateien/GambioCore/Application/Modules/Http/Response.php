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

namespace Gambio\Core\Application\Modules\Http;

use InvalidArgumentException;

/**
 * Interface Response
 * @package Gambio\Core\Contracts\ModuleFoo\Http
 */
interface Response
{
    /**
     * Writes the data to the response.
     *
     * @param string $data
     *
     * @return $this
     */
    public function write(string $data): self;
    
    
    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param string          $name  Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     *
     * @return static
     * @throws InvalidArgumentException for invalid header names or values.
     */
    public function withHeader(string $name, $value): self;
    
    
    /**
     * Write JSON to Response Body.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * This method prepares the response object to return an HTTP Json
     * response to the client.
     *
     * @param mixed    $data    The data
     * @param int|null $status  The HTTP status code
     * @param int      $options Json encoding options
     * @param int      $depth   Json encoding max depth
     *
     * @return $this
     */
    public function withJson($data, ?int $status = null, int $options = 0, int $depth = 512): self;
    
    
    /**
     * Redirect to specified location
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     *
     * @param string   $url    The redirect destination.
     * @param int|null $status The redirect HTTP status code.
     *
     * @return self
     */
    public function withRedirect(string $url, ?int $status = null): self;
}