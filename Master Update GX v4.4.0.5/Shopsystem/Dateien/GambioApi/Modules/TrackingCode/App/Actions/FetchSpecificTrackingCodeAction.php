<?php
/* --------------------------------------------------------------
   FetchSpecificTrackingCodeAction.php 2020-09-01
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Api\Modules\TrackingCode\App\Actions;

use Gambio\Admin\Modules\TrackingCode\Model\Exceptions\TrackingCodeNotFoundException;
use Gambio\Admin\Modules\TrackingCode\Services\TrackingCodeReadService;
use Gambio\Api\Application\Responses\CreateApiMetaDataTrait;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

/**
 * Class FetchSpecificTrackingCodeAction
 *
 * @package Gambio\Api\Modules\TrackingCode\App\Actions
 */
class FetchSpecificTrackingCodeAction
{
    use CreateApiMetaDataTrait;
    
    /**
     * @var TrackingCodeReadService
     */
    private $service;
    
    
    /**
     * FetchSpecificTrackingCodeAction constructor.
     *
     * @param TrackingCodeReadService $service
     */
    public function __construct(TrackingCodeReadService $service)
    {
        $this->service = $service;
    }
    
    
    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $trackingCodeId = (int)$request->getAttribute('id');
            $trackingCode   = $this->service->getTrackingCodeById($trackingCodeId);
            $metaData       = $this->createApiMetaData();
            
            return $response->withJson([
                                           'data'  => $trackingCode->toArray(),
                                           '_meta' => $metaData,
                                       ]);
        } catch (TrackingCodeNotFoundException $e) {
            return $response->withStatus(404);
        }
    }
}