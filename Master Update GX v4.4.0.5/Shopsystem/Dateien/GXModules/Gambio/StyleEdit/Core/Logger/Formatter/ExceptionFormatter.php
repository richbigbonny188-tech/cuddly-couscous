<?php
/*--------------------------------------------------------------
   ExceptionFormatter.php 2020-09-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\StyleEdit\Core\Logger\Formatter;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Class ExceptionFormatter
 * @package Gambio\StyleEdit\Core\Logger
 */
class ExceptionFormatter
{
    /**
     * @param ServerRequestInterface $request
     * @param Throwable              $exception
     *
     * @return string
     */
    public function format(ServerRequestInterface $request, Throwable $exception): string
    {
        return
          PHP_EOL . 'Path: ' . $request->getUri()->getPath()
        . PHP_EOL . 'Get: ' . json_encode($_GET)
        . PHP_EOL . 'Post: ' . json_encode($_POST)
        . PHP_EOL . 'Message: ' . $exception->getMessage()
        . PHP_EOL . 'Code: ' . $exception->getCode()
        . PHP_EOL . 'File: ' . $exception->getFile()
        . PHP_EOL . 'Line: ' . $exception->getLine()
        . PHP_EOL . 'Stacktrace:' . PHP_EOL . $exception->getTraceAsString();
    }
}