<?php
/* --------------------------------------------------------------
   TextPhrasesServiceProvider.php 2020-04-23
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
use Gambio\Core\Application\ValueObjects\UserPreferences;
use Gambio\Core\Cache\CacheFactory;
use Gambio\Core\Application\DependencyInjection\AbstractServiceProvider;
use Gambio\Core\Language\Repositories\TextPhraseReader;

/**
 * Class TextPhrasesServiceProvider
 *
 * @package Gambio\Core\Language
 */
class TextPhrasesServiceProvider extends AbstractServiceProvider
{
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            TextManager::class,
            TextPhraseRepository::class,
        ];
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->application->register(TextPhraseReader::class)->addArgument(Connection::class);
        $this->application->registerShared(
            TextPhraseRepository::class,
            function () {
                /** @var CacheFactory $cacheFactory */
                $cacheFactory = $this->application->get(CacheFactory::class);
                
                return new Repositories\TextPhraseRepository(
                    $cacheFactory->createCacheFor('text_cache'), $this->application->get(TextPhraseReader::class)
                );
            }
        );
        
        $this->application->registerShared(TextManager::class, Services\TextManager::class)->addArgument(
                TextPhraseRepository::class
            )->addArgument(UserPreferences::class);
    }
}