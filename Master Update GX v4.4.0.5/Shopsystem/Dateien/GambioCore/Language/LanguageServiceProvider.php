<?php
/* --------------------------------------------------------------
   LanguageSerivceProvider.php 2020-04-16
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\Language;

use Doctrine\DBAL\Connection;
use Gambio\Core\Application\DependencyInjection\AbstractServiceProvider;
use Gambio\Core\Language\Repositories\LanguageMapper;
use Gambio\Core\Language\Repositories\LanguageReader;

/**
 * Class LanguageServiceProvider
 *
 * @package Gambio\Core\Language
 */
class LanguageServiceProvider extends AbstractServiceProvider
{
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            LanguageService::class,
            LanguageRepository::class,
        ];
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->application->register(LanguageFactory::class);
        $this->application->register(LanguageMapper::class)->addArgument(LanguageFactory::class);
        $this->application->register(LanguageReader::class)->addArgument(Connection::class);
        
        $this->application->registerShared(LanguageRepository::class, Repositories\LanguageRepository::class)
                          ->addArgument(LanguageMapper::class)
                          ->addArgument(LanguageReader::class);
        
        $this->application->registerShared(LanguageService::class, Services\LanguageService::class)->addArgument(
                LanguageRepository::class
            );
    }
}