<?php
/* --------------------------------------------------------------
   FetchSpecificWithdrawalAction.php 2020-08-26
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Api\Modules\Withdrawal\App\Actions;

use Gambio\Admin\Modules\Withdrawal\Model\Exceptions\WithdrawalNotFoundException;
use Gambio\Admin\Modules\Withdrawal\Services\WithdrawalReadService;
use Gambio\Api\Application\Responses\CreateApiMetaDataTrait;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

/**
 * Class FetchSpecificWithdrawalAction
 *
 * @package Gambio\Api\Modules\Withdrawal\App\Actions
 */
class FetchSpecificWithdrawalAction
{
    use CreateApiMetaDataTrait;
    
    /**
     * @var WithdrawalReadService
     */
    private $service;
    
    
    /**
     * FetchSpecificWithdrawalAction constructor.
     *
     * @param WithdrawalReadService $service
     */
    public function __construct(WithdrawalReadService $service)
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
            $withdrawalId = (int)$request->getAttribute('id');
            $withdrawal   = $this->service->getWithdrawalById($withdrawalId);
            $metaData     = $this->createApiMetaData();
            
            return $response->withJson([
                                           'data'  => $withdrawal->toArray(),
                                           '_meta' => $metaData,
                                       ]);
        } catch (WithdrawalNotFoundException $e) {
            return $response->withStatus(404);
        }
    }
}