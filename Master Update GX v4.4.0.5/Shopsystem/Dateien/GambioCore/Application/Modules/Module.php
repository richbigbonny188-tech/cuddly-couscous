<?php
/* --------------------------------------------------------------
 Module.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Modules;

/**
 * Interface Module
 * @package Gambio\Core\Framework\Module
 */
interface Module
{
    /**
     * List of event listeners.
     *
     * The list must be a multidimensional array in the following format:
     * (Fqn means full qualified class name).
     * key = Fqn event class name
     * value = numeric, one dimensional array with fqn event listener names
     *
     * Example:
     *
     * ```php
     * use Vendor\Library\FooEvent;
     * use Vendor\Library\FooEventListenerOne;
     * use Vendor\Library\FooEventListenerTwo;
     *
     * $eventListeners = [
     *      FooEvent::class =>
     *          [
     *              FooEventListenerOne::class,
     *              FooEventListenerTwo::class,
     *          ]
     * ]
     * ```
     *
     * @return array|null
     */
    public function eventListeners(): ?array;
    
    
    /**
     * List of command handlers.
     *
     * The list must be a multidimensional array in the following format:
     * (Fqn means full qualified class name).
     * key = Fqn event class name
     * value = numeric, one dimensional array with fqn command handler names
     * Example:
     *
     * ```php
     * use Vendor\Library\FooCommand;
     * use Vendor\Library\FooCommandHandlerOne;
     * use Vendor\Library\FooCommandHandlerTwo;
     *
     * $commandHandlers = [
     *      FooCommand::class =>
     *          [
     *              FooCommandHandlerOne::class,
     *              FooCommandHandlerTwo::class,
     *          ]
     * ]
     * ```
     *
     * @return array|null
     */
    public function commandHandlers(): ?array;
    
    
    /**
     * List of HTTP-GET routes.
     *
     * The list must be a multidimensional array in the following format:
     * (Fqn means full qualified class name).
     * key = route, usually beginning with /.
     * value = associative, one dimensional array with dispatch callback information.
     *
     * Example:
     * ```php
     * use Vendor\Library\MyModuleController;
     *
     * $getRoutes = [
     *      '/my-module'              => [MyModuleController:class, 'index'],
     *      '/my-module/get/{someId}' => [MyModuleController:class, 'find'],
     * ];
     * ```
     *
     * @return array|null
     */
    public function getRoutes(): ?array;
    
    
    /**
     * List of HTTP-POST routes.
     *
     * The list must be a multidimensional array in the following format:
     * (Fqn means full qualified class name).
     * key = route, usually beginning with /.
     * value = associative, one dimensional array with dispatch callback information.
     *
     * Example:
     * ```php
     * use Vendor\Library\MyModuleSaveAction;
     *
     * $postRoutes = [
     *      '/my-module/save' => [MyModuleSaveAction:class],
     * ];
     * ```
     *
     * @return array|null
     */
    public function postRoutes(): ?array;
    
    
    /**
     * List of HTTP-PUT routes.
     *
     * The list must be a multidimensional array in the following format:
     * (Fqn means full qualified class name).
     * key = route, usually beginning with /.
     * value = associative, one dimensional array with dispatch callback information.
     *
     * Example:
     * ```php
     * use Vendor\Library\MyModulePutAction;
     *
     * $putRoutes = [
     *      '/my-module/save' => [MyModulePutAction:class],
     * ];
     * ```
     *
     * @return array|null
     */
    public function putRoutes(): ?array;
    
    
    /**
     * List of HTTP-DELETE routes.
     *
     * The list must be a multidimensional array in the following format:
     * (Fqn means full qualified class name).
     * key = route, usually beginning with /.
     * value = associative, one dimensional array with dispatch callback information.
     *
     * Example:
     * ```php
     * use Vendor\Library\MyModuleDeleteAction;
     *
     * $deleteRoutes = [
     *      '/my-module/save' => [MyModuleDeleteAction:class],
     * ];
     * ```
     *
     * @return array|null
     */
    public function deleteRoutes(): ?array;
    
    
    /**
     * List of external dependencies.
     *
     * If the module depends on any external dependencies, the must be declared here.
     * Todo: Refine documentation.
     */
    public function dependsOn(): ?array;
}