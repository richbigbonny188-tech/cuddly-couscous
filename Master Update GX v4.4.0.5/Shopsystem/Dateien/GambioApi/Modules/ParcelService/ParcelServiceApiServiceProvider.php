<?php
/* --------------------------------------------------------------
   ParcelServiceApiServiceProvider.php 2020-08-28
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Api\Modules\ParcelService;

use Gambio\Admin\Modules\ParcelService\Services\ParcelServiceFactory;
use Gambio\Admin\Modules\ParcelService\Services\ParcelServiceFilterService;
use Gambio\Admin\Modules\ParcelService\Services\ParcelServiceReadService;
use Gambio\Admin\Modules\ParcelService\Services\ParcelServiceWriteService;
use Gambio\Api\Modules\ParcelService\App\Actions\CreateParcelServicesAction;
use Gambio\Api\Modules\ParcelService\App\Actions\DeleteParcelServicesAction;
use Gambio\Api\Modules\ParcelService\App\Actions\FetchAllParcelServicesAction;
use Gambio\Api\Modules\ParcelService\App\Actions\FetchSpecificParcelServiceAction;
use Gambio\Api\Modules\ParcelService\App\Actions\UpdateParcelServicesAction;
use Gambio\Api\Modules\ParcelService\App\ParcelServiceApiRequestParser;
use Gambio\Api\Modules\ParcelService\App\ParcelServiceApiRequestValidator;
use Gambio\Core\Application\DependencyInjection\AbstractServiceProvider;

/**
 * Class ParcelServiceApiServiceProvider
 *
 * @package Gambio\Api\Modules\ParcelService
 * @codeCoverageIgnore
 */
class ParcelServiceApiServiceProvider extends AbstractServiceProvider
{
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            CreateParcelServicesAction::class,
            DeleteParcelServicesAction::class,
            FetchSpecificParcelServiceAction::class,
            FetchAllParcelServicesAction::class,
            UpdateParcelServicesAction::class,
        ];
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->application->registerShared(ParcelServiceApiRequestParser::class)->addArgument(ParcelServiceFactory::class);
        
        $this->application->registerShared(ParcelServiceApiRequestValidator::class);
        
        $this->application->registerShared(CreateParcelServicesAction::class)
            ->addArgument(ParcelServiceApiRequestParser::class)
            ->addArgument(ParcelServiceApiRequestValidator::class)
            ->addArgument(ParcelServiceWriteService::class);
        
        $this->application->registerShared(DeleteParcelServicesAction::class)->addArgument(ParcelServiceWriteService::class);
        
        $this->application->registerShared(FetchSpecificParcelServiceAction::class)
            ->addArgument(ParcelServiceReadService::class);
        
        $this->application->registerShared(FetchAllParcelServicesAction::class)
            ->addArgument(ParcelServiceApiRequestParser::class)
            ->addArgument(ParcelServiceFilterService::class);
        
        $this->application->registerShared(UpdateParcelServicesAction::class)
            ->addArgument(ParcelServiceApiRequestValidator::class)
            ->addArgument(ParcelServiceApiRequestParser::class)
            ->addArgument(ParcelServiceWriteService::class)
            ->addArgument(ParcelServiceReadService::class)
            ->addArgument(ParcelServiceFactory::class);
    }
}