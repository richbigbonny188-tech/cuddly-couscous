<?php
/* --------------------------------------------------------------
 SmartyEngine.php 2020-09-03
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\TemplateEngine\Engines\Smarty;

use Exception;
use Gambio\Core\TemplateEngine\Exceptions\RenderingFailedException;
use Gambio\Core\TemplateEngine\TemplateEngine;
use Smarty;

/**
 * Class SmartyEngine
 * @package Gambio\Core\TemplateEngine\Engines
 */
class SmartyEngine implements TemplateEngine
{
    /**
     * @var Smarty
     */
    private $smarty;
    
    /**
     * @var TemplateExtender
     */
    private $templateExtender;
    
    /**
     * @var SmartyConfiguration
     */
    private $configuration;
    
    
    /**
     * SmartyEngine constructor.
     *
     * @param Smarty              $smarty
     * @param TemplateExtender    $templateExtender
     * @param SmartyConfiguration $configuration
     */
    public function __construct(
        Smarty $smarty,
        TemplateExtender $templateExtender,
        SmartyConfiguration $configuration
    ) {
        $this->smarty           = $smarty;
        $this->templateExtender = $templateExtender;
        $this->configuration    = $configuration;
    }
    
    
    /**
     * @inheritDoc
     */
    public function render(string $templatePath, array $data = []): string
    {
        $this->configuration->load($this->smarty);
        foreach ($data as $key => $value) {
            $this->smarty->assign($key, $value);
        }
        
        try {
            $template = $this->templateExtender->extend($templatePath);
            
            return $this->smarty->fetch($template);
        } catch (Exception $e) {
            $message = "Could not render template ({$templatePath}).\n{$e->getMessage()}";
            
            throw new RenderingFailedException($message, $e->getCode(), $e);
        }
    }
}