<?php
/* --------------------------------------------------------------
   TrackingCodeApiServiceProvider.php 2020-09-01
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Api\Modules\TrackingCode;

use Gambio\Admin\Modules\TrackingCode\Services\TrackingCodeFactory;
use Gambio\Admin\Modules\TrackingCode\Services\TrackingCodeFilterService;
use Gambio\Admin\Modules\TrackingCode\Services\TrackingCodeReadService;
use Gambio\Admin\Modules\TrackingCode\Services\TrackingCodeWriteService;
use Gambio\Api\Modules\TrackingCode\App\Actions\CreateTrackingCodesAction;
use Gambio\Api\Modules\TrackingCode\App\Actions\DeleteTrackingCodesAction;
use Gambio\Api\Modules\TrackingCode\App\Actions\FetchAllTrackingCodesAction;
use Gambio\Api\Modules\TrackingCode\App\Actions\FetchSpecificTrackingCodeAction;
use Gambio\Api\Modules\TrackingCode\App\TrackingCodeApiRequestParser;
use Gambio\Api\Modules\TrackingCode\App\TrackingCodeApiRequestValidator;
use Gambio\Core\Application\DependencyInjection\AbstractServiceProvider;

/**
 * Class TrackingCodeApiServiceProvider
 *
 * @package Gambio\Api\Modules\TrackingCode
 * @codeCoverageIgnore
 */
class TrackingCodeApiServiceProvider extends AbstractServiceProvider
{
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            CreateTrackingCodesAction::class,
            DeleteTrackingCodesAction::class,
            FetchSpecificTrackingCodeAction::class,
            FetchAllTrackingCodesAction::class,
        ];
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->application->registerShared(TrackingCodeApiRequestParser::class)->addArgument(TrackingCodeFactory::class);
        
        $this->application->registerShared(TrackingCodeApiRequestValidator::class);
        
        $this->application->registerShared(CreateTrackingCodesAction::class)
            ->addArgument(TrackingCodeApiRequestParser::class)
            ->addArgument(TrackingCodeApiRequestValidator::class)
            ->addArgument(TrackingCodeWriteService::class);
        
        $this->application->registerShared(DeleteTrackingCodesAction::class)->addArgument(TrackingCodeWriteService::class);
        
        $this->application->registerShared(FetchSpecificTrackingCodeAction::class)->addArgument(TrackingCodeReadService::class);
        
        $this->application->registerShared(FetchAllTrackingCodesAction::class)
            ->addArgument(TrackingCodeApiRequestParser::class)
            ->addArgument(TrackingCodeFilterService::class);
    }
}