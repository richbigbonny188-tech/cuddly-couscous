<?php
/**
 * ServiceProvider.php 2020-08-05
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2020 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Database\Image;

use Gambio\Shop\SellingUnit\Images\Builders\CollectionBuilder;
use Gambio\Shop\SellingUnit\Images\ValueObjects\SelectedCollectionType;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * Class ServiceProvider
 *
 * @package Gambio\Shop\SellingUnit\Database\Image
 * @property-read Container $container
 * @codeCoverageIgnore
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $provides = [
        CollectionBuilder::class,
        SelectedCollectionType::class
    ];

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->container->share(CollectionBuilder::class);
        $this->container->share(SelectedCollectionType::class)->addArgument(CollectionBuilder::class);
    }


    /**
     * @inheritDoc
     */
    public function boot(): void
    {

    }

}