<?php
/* --------------------------------------------------------------
 routes.php 2020-10-05
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

use Gambio\Api\Application\BaseApiV3Controller;
use Gambio\Api\Modules\ParcelService\App\Actions\CreateParcelServicesAction;
use Gambio\Api\Modules\ParcelService\App\Actions\DeleteParcelServicesAction;
use Gambio\Api\Modules\ParcelService\App\Actions\FetchAllParcelServicesAction;
use Gambio\Api\Modules\ParcelService\App\Actions\FetchSpecificParcelServiceAction;
use Gambio\Api\Modules\ParcelService\App\Actions\UpdateParcelServicesAction;
use Gambio\Api\Modules\TrackingCode\App\Actions\CreateTrackingCodesAction;
use Gambio\Api\Modules\TrackingCode\App\Actions\DeleteTrackingCodesAction;
use Gambio\Api\Modules\TrackingCode\App\Actions\FetchAllTrackingCodesAction;
use Gambio\Api\Modules\TrackingCode\App\Actions\FetchSpecificTrackingCodeAction;
use Gambio\Api\Modules\Withdrawal\App\Actions\CreateWithdrawalsAction;
use Gambio\Api\Modules\Withdrawal\App\Actions\DeleteWithdrawalsAction;
use Gambio\Api\Modules\Withdrawal\App\Actions\FetchAllWithdrawalsAction;
use Gambio\Api\Modules\Withdrawal\App\Actions\FetchSpecificWithdrawalAction;
use Gambio\Api\Modules\Withdrawal\App\Actions\PatchWithdrawalsAction;
use Slim\App as SlimApp;
use Slim\Routing\RouteCollectorProxy;

return static function (SlimApp $slimApp) {
    $slimApp->group('/api.php/v3',
        function (RouteCollectorProxy $group) {
            /**
             * WITHDRAWALS ENDPOINTS
             */
            $group->group('/withdrawals',
                function (RouteCollectorProxy $group) {
                    $group->get('', FetchAllWithdrawalsAction::class);
                    $group->post('', CreateWithdrawalsAction::class);
                    $group->patch('', PatchWithdrawalsAction::class);
                    $group->delete('/{ids:[0-9,]+}', DeleteWithdrawalsAction::class);
                    $group->get('/{id:[0-9]+}', FetchSpecificWithdrawalAction::class);
                });
            /**
             * PARCEL SERVICES ENDPOINTS
             */
            $group->group('/parcel-services',
                function (RouteCollectorProxy $group) {
                    $group->get('', FetchAllParcelServicesAction::class);
                    $group->post('', CreateParcelServicesAction::class);
                    $group->put('', UpdateParcelServicesAction::class);
                    $group->delete('/{ids:[0-9,]+}', DeleteParcelServicesAction::class);
                    $group->get('/{id:[0-9]+}', FetchSpecificParcelServiceAction::class);
                });
            /**
             * TRACKING CODES ENDPOINTS
             */
            $group->group('/tracking-codes',
                function (RouteCollectorProxy $group) {
                    $group->get('', FetchAllTrackingCodesAction::class);
                    $group->post('', CreateTrackingCodesAction::class);
                    $group->delete('/{ids:[0-9,]+}', DeleteTrackingCodesAction::class);
                    $group->get('/{id:[0-9]+}', FetchSpecificTrackingCodeAction::class);
                });
            /**
             * BASE ENDPOINT
             */
            $group->get('', BaseApiV3Controller::class);
        });
};
