<?php
/* --------------------------------------------------------------
 SessionRepository.php 2020-06-05
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Session;

use Gambio\Core\Application\ValueObjects\Path;

/**
 * Class SessionRepository
 * @package Gambio\Core\Session\Repository
 */
class SessionRepository
{
    /**
     * @var SessionNamePostfixGenerator
     */
    private $postfixGenerator;
    
    /**
     * @var Path
     */
    private $path;
    
    
    /**
     * SessionRepository constructor.
     *
     * @param SessionNamePostfixGenerator $postfixGenerator
     * @param Path                        $path
     */
    public function __construct(SessionNamePostfixGenerator $postfixGenerator, Path $path)
    {
        $this->postfixGenerator = $postfixGenerator;
        $this->path             = $path;
    }
    
    
    /**
     * @param string $host
     * @param string $webPath
     * @param string $serverPath
     *
     * @return string
     */
    public function getNamePostfix(string $host, string $webPath, string $serverPath): string
    {
        $identifier   = implode('|', [$host, $webPath, $serverPath]);
        $path         = "{$this->path->base()}/media/session_name_postfix_";
        $postfixFiles = glob("{$path}*");
        
        if (empty($postfixFiles)) {
            return $this->createAndReturnPostfix($identifier, $path);
        }
        $this->removeAdditionalPostfixFiles($postfixFiles);
        
        $postfixFile    = $postfixFiles[0];
        $postfixContent = file_get_contents($postfixFile);
        
        if ($postfixContent !== $identifier) {
            unlink($postfixFile);
            
            return $this->createAndReturnPostfix($identifier, $path);
        }
        
        return str_replace('session_name_postfix_', '', basename($postfixFile));
    }
    
    
    /**
     * Creates a new postfix file and returns the postfix string.
     *
     * @param string $identifier
     * @param string $path
     *
     * @return string
     */
    private function createAndReturnPostfix(string $identifier, string $path): string
    {
        $postfix         = $this->postfixGenerator->generate();
        $postfixFilePath = "{$path}{$postfix}";
        file_put_contents($postfixFilePath, $identifier);
        
        return $postfix;
    }
    
    
    /**
     * Deletes any additional postfix files.
     *
     * @param array $postfixFiles
     */
    private function removeAdditionalPostfixFiles(array $postfixFiles): void
    {
        if (count($postfixFiles) > 1) {
            foreach (array_slice($postfixFiles, 1) as $extraFile) {
                unlink($extraFile);
            }
        }
    }
}