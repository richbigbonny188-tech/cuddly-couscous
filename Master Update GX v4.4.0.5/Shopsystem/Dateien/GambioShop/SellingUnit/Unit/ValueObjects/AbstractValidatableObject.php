<?php
/*--------------------------------------------------------------------------------------------------
    AbstractValidatableObject.php 2020-12-01
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Unit\ValueObjects;

use Exception;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\ExceptionStacker;

abstract class AbstractValidatableObject implements ExceptionStacker
{
    /**
     * @var ?Exception
     */
    protected $exception;
    /**
     *
     */
    protected $exceptionList = [];
    
    
    /**
     * @return Exception|null
     */
    public function exception(): ?Exception
    {
        return $this->exception;
    }
    
    
    /**
     *
     * @param null $exceptionClass
     *
     * @return Exception[]
     */
    public function exceptions($exceptionClass = null)
    {
        if (isset($this->exceptionList[$exceptionClass])) {
            return $this->exceptionList[$exceptionClass];
        } elseif (!empty($this->exceptionList)) {
            return array_merge(...array_values($this->exceptionList));
        } else {
            return [];
        }
    }
    
    
    /**
     * @param string $exceptionName
     *
     * @return bool
     */
    public function hasException(string $exceptionName)
    {
        return isset($this->exceptionList[$exceptionName]);
    }
    
    
    /**
     * @param Exception $exception
     */
    protected function flattenExceptionBacktrace(Exception $exception): void
    {
        try {
            
            $traceProperty = (new \ReflectionClass('Exception'))->getProperty('trace');
            $traceProperty->setAccessible(true);
            
            $flatten = static function (&$value, $key) {
                if ($value instanceof \Closure) {
                    $closureReflection = new \ReflectionFunction($value);
                    $value             = sprintf('(Closure at %s:%s)',
                                                 $closureReflection->getFileName(),
                                                 $closureReflection->getStartLine());
                } elseif (is_object($value)) {
                    $value = sprintf('object(%s)', get_class($value));
                } elseif (is_resource($value)) {
                    $value = sprintf('resource(%s)', get_resource_type($value));
                }
            };
            
            do {
                $trace = $traceProperty->getValue($exception);
                foreach ($trace as &$call) {
                    
                    if (null !== $call['args']) {
                        
                        array_walk_recursive($call['args'], $flatten);
                    }
                }
                unset($call);
                $traceProperty->setValue($exception, $trace);
            } while ($exception = $exception->getPrevious());
            
            $traceProperty->setAccessible(false);
        } catch (Exception $e) {
            //just ignore
        }
    }
    
    
    /**
     * @param Exception $exception
     */
    public function stackException(Exception $exception): void
    {
        if (!isset($this->exceptionList[get_class($exception)])) {
            $this->exceptionList[get_class($exception)] = [];
        }
        $this->flattenExceptionBacktrace($exception);
        
        $this->exception                              = $exception;
        $this->exceptionList[get_class($exception)][] = $exception;
    }
}