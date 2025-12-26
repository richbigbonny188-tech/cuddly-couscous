<?php
/* --------------------------------------------------------------
   FetchSpecificParcelServiceAction.php 2020-08-28
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Api\Modules\ParcelService\App\Actions;

use Gambio\Admin\Modules\ParcelService\Model\Exceptions\ParcelServiceNotFoundException;
use Gambio\Admin\Modules\ParcelService\Services\ParcelServiceReadService;
use Gambio\Api\Application\Responses\CreateApiMetaDataTrait;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

/**
 * Class FetchSpecificParcelServiceAction
 *
 * @package Gambio\Api\Modules\ParcelService\App\Actions
 */
class FetchSpecificParcelServiceAction
{
    use CreateApiMetaDataTrait;
    
    /**
     * @var ParcelServiceReadService
     */
    private $service;
    
    
    /**
     * FetchSpecificParcelServiceAction constructor.
     *
     * @param ParcelServiceReadService $service
     */
    public function __construct(ParcelServiceReadService $service)
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
            $parcelServiceId = (int)$request->getAttribute('id');
            $parcelService   = $this->service->getParcelServiceById($parcelServiceId);
            $metaData        = $this->createApiMetaData();
            
            return $response->withJson([
                                           'data'  => $parcelService->toArray(),
                                           '_meta' => $metaData,
                                       ]);
        } catch (ParcelServiceNotFoundException $e) {
            return $response->withStatus(404);
        }
    }
}