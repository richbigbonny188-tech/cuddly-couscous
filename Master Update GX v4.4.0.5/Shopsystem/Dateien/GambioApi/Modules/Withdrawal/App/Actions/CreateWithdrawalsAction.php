<?php
/* --------------------------------------------------------------
   CreateWithdrawalsAction.php 2020-08-24
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Api\Modules\Withdrawal\App\Actions;

use Gambio\Admin\Modules\Withdrawal\Services\WithdrawalWriteService;
use Gambio\Api\Application\Responses\CreateApiMetaDataTrait;
use Gambio\Api\Modules\Withdrawal\App\WithdrawalApiRequestParser;
use Gambio\Api\Modules\Withdrawal\App\WithdrawalApiRequestValidator;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

/**
 * Class CreateWithdrawalsAction
 *
 * @package Gambio\Api\Modules\Withdrawal\App\Actions
 */
class CreateWithdrawalsAction
{
    use CreateApiMetaDataTrait;
    
    /**
     * @var WithdrawalApiRequestParser
     */
    private $requestParser;
    
    /**
     * @var WithdrawalApiRequestValidator
     */
    private $requestValidator;
    
    /**
     * @var WithdrawalWriteService
     */
    private $service;
    
    
    /**
     * CreateWithdrawalsAction constructor.
     *
     * @param WithdrawalApiRequestParser    $requestParser
     * @param WithdrawalApiRequestValidator $requestValidator
     * @param WithdrawalWriteService        $service
     */
    public function __construct(
        WithdrawalApiRequestParser $requestParser,
        WithdrawalApiRequestValidator $requestValidator,
        WithdrawalWriteService $service
    ) {
        $this->requestParser    = $requestParser;
        $this->requestValidator = $requestValidator;
        $this->service          = $service;
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
        $errors = $this->requestValidator->validatePostRequestBody($request->getParsedBody());
        if (count($errors) > 0) {
            return $response->withStatus(400)->withJson(['errors' => $errors]);
        }
        
        $withdrawalsData = $this->requestParser->parseWithdrawalDataForCreation($request, $errors);
        if (count($errors) > 0) {
            return $response->withStatus(422)->withJson(['errors' => $errors]);
        }
        $ids = $this->service->createMultipleWithdrawals($withdrawalsData['orders'],
                                                         $withdrawalsData['customers'],
                                                         $withdrawalsData['dates'],
                                                         $withdrawalsData['contents'],
                                                         $withdrawalsData['createdByAdminFlags']);
        
        $links   = [];
        $baseUrl = rtrim($this->requestParser->getResourceUrlFromRequest($request), '/');
        foreach ($ids as $id) {
            $links[] = $baseUrl . '/withdrawals/' . $id->value();
        }
        
        $metaData = $this->createApiMetaData($links);
        
        return $response->withJson([
                                       'data'  => $ids->toArray(),
                                       '_meta' => $metaData,
                                   ],
                                   201);
    }
}