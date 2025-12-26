<?php
/* --------------------------------------------------------------
   TextFormatter.php 2020-04-21
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\Logging\Formatter;

use Monolog\Formatter\NormalizerFormatter;

/**
 * Class TextFormatter
 *
 * @package Gambio\Core\Logger
 * @codeCoverageIgnore
 */
class TextFormatter extends NormalizerFormatter
{
    private const DEFAULT_FILE_INTEND = '  ';
    
    
    /**
     * HtmlFormatter constructor.
     */
    public function __construct()
    {
        parent::__construct('Y-m-d H:i:s');
    }
    
    
    /**
     * Formats a log record.
     *
     * @param array $record
     *
     * @return string
     */
    public function format(array $record): string
    {
        $text = PHP_EOL . '######################################################################' . PHP_EOL . PHP_EOL;
        $text .= '[' . $record['datetime']->format($this->dateFormat) . ' | ' . strtoupper($record['level_name'])
                 . '] ';
        $text .= $record['message'] . PHP_EOL;
        $text .= $this->formatArray($record['context'] ?? [], "", 'context');
        $text .= $this->formatArray($record['extra'] ?? [], "", 'extra');
        
        return $text;
    }
    
    
    /**
     * @param array  $array
     * @param string $intend
     * @param string $name
     *
     * @return string
     */
    private function formatArray(
        array $array,
        string $intend = self::DEFAULT_FILE_INTEND,
        string $name = ''
    ): string {
        if (count($array) === 0) {
            return '';
        }
        
        $text   = ($name !== '') ? $intend . $name . ':' . PHP_EOL : PHP_EOL;
        $intend .= self::DEFAULT_FILE_INTEND;
        
        foreach ($array as $key => $value) {
            if ($value === null) {
                continue;
            }
            
            $value = $this->normalize($value);
            
            if (is_array($value)) {
                $text .= $this->formatArray($value, $intend, (string)$key);
            } else {
                if (strpos((string)$value, PHP_EOL) > 0) {
                    $value = str_replace(PHP_EOL, PHP_EOL . $intend . self::DEFAULT_FILE_INTEND, (string)$value);
                }
                
                $text .= $intend . $key . ': ' . $value . PHP_EOL;
            }
        }
        
        return $text;
    }
}