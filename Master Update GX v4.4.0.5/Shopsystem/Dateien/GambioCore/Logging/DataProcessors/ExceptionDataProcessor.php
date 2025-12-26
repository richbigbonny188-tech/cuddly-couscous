<?php
/* --------------------------------------------------------------
   ExceptionDataProcessor.php 2020-08-24
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\Logging\DataProcessors;

use Exception;
use Monolog\Processor\ProcessorInterface;

/**
 * Class ExceptionDataProcessor
 *
 * @package Gambio\Core\Logger
 */
class ExceptionDataProcessor implements ProcessorInterface
{
    /**
     * @var bool
     */
    private $addCodeSnippets;
    
    
    /**
     * ExceptionHydrationProcessor constructor.
     *
     * @param bool $addCodeSnippets
     */
    public function __construct($addCodeSnippets = false)
    {
        $this->addCodeSnippets = $addCodeSnippets;
    }
    
    
    /**
     * @param array $recordData
     *
     * @return array
     */
    public function __invoke(array $recordData): array
    {
        foreach ($recordData['context'] ?? [] as $index => $value) {
            if (is_object($value) && $value instanceof Exception) {
                $this->updateExceptionData($recordData['context'][$index]);
            }
        }
        
        return $recordData;
    }
    
    
    /**
     * @param Exception|array $exception
     */
    private function updateExceptionData(Exception &$exception): void
    {
        /** @var Exception $exceptionBackup */
        $exceptionBackup = $exception;
        
        $exception = [
            'class'   => get_class($exceptionBackup),
            'message' => $exceptionBackup->getMessage(),
            'code'    => $exceptionBackup->getCode(),
            'file'    => $exceptionBackup->getFile(),
            'line'    => $exceptionBackup->getLine(),
            'trace'   => [],
        ];
        
        foreach ($exceptionBackup->getTrace() as $index => $trace) {
            $occurrence = ($trace['class'] ?? '') . '::' . $trace['function'];
            $snippet    = null;
            if (array_key_exists('file', $trace) && array_key_exists('line', $trace)) {
                $occurrence = $trace['file'] . ':' . $trace['line'];
                $snippet    = $this->getCodeSnippet($trace['file'], $trace['line']);
            }
            
            $exception['trace'][] = $occurrence . ($snippet !== null ? PHP_EOL . $snippet : '');
        }
    }
    
    
    /**
     * @param string $file
     * @param int    $line
     *
     * @return string
     */
    private function getCodeSnippet(string $file, int $line): ?string
    {
        if ($this->addCodeSnippets === false) {
            return null;
        }
        
        $lines = file($file);
        
        $snippet = (isset($lines[$line - 3]) ? '│  ' . $lines[$line - 3] : '');
        $snippet .= (isset($lines[$line - 2]) ? '│  ' . $lines[$line - 2] : '');
        $snippet .= (isset($lines[$line - 1]) ? '├─ ' . $lines[$line - 1] : '');
        $snippet .= (isset($lines[$line]) ? '│  ' . $lines[$line] : '');
        $snippet .= (isset($lines[$line + 1]) ? '│  ' . $lines[$line + 1] : '');
        
        return rtrim($snippet, PHP_EOL);
    }
}