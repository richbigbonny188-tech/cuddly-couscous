<?php
/* --------------------------------------------------------------
   PatchWithdrawalsAction.php 2020-08-24
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Api\Modules\Withdrawal\App\Actions;

use Exception;
use Gambio\Admin\Modules\Withdrawal\Services\WithdrawalFactory;
use Gambio\Admin\Modules\Withdrawal\Services\WithdrawalReadService;
use Gambio\Admin\Modules\Withdrawal\Services\WithdrawalWriteService;
use Gambio\Api\Modules\Withdrawal\App\WithdrawalApiRequestParser;
use Gambio\Api\Modules\Withdrawal\App\WithdrawalApiRequestValidator;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

/**
 * Class PatchWithdrawalsAction
 *
 * @package Gambio\Api\Modules\Withdrawal\App\Actions
 */
class PatchWithdrawalsAction
{
    /**
     * @var WithdrawalApiRequestValidator
     */
    private $requestValidator;
    
    /**
     * @var WithdrawalApiRequestParser
     */
    private $requestParser;
    
    /**
     * @var WithdrawalWriteService
     */
    private $writeService;
    
    /**
     * @var WithdrawalReadService
     */
    private $readService;
    
    /**
     * @var WithdrawalFactory
     */
    private $factory;
    
    
    /**
     * PatchWithdrawalsAction constructor.
     *
     * @param WithdrawalApiRequestValidator $requestValidator
     * @param WithdrawalApiRequestParser    $requestParser
     * @param WithdrawalWriteService        $writeService
     * @param WithdrawalReadService         $readService
     * @param WithdrawalFactory             $factory
     */
    public function __construct(
        WithdrawalApiRequestValidator $requestValidator,
        WithdrawalApiRequestParser $requestParser,
        WithdrawalWriteService $writeService,
        WithdrawalReadService $readService,
        WithdrawalFactory $factory
    ) {
        $this->requestValidator = $requestValidator;
        $this->requestParser    = $requestParser;
        $this->writeService     = $writeService;
        $this->readService      = $readService;
        $this->factory          = $factory;
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
        $errors = $this->requestValidator->validatePutRequestBody($request->getParsedBody());
        if (count($errors) > 0) {
            return $response->withStatus(400)->withJson(['errors' => $errors]);
        }
        
        $changedOrderIds = $this->requestParser->parseChangedOrderIds($request, $errors);
        $withdrawals     = [];
        foreach ($changedOrderIds as $index => $changedOrderId) {
            try {
                $withdrawal = $this->readService->getWithdrawalById($changedOrderId['withdrawalId']);
                $withdrawal->changeOrderId($this->factory->createOrderId($changedOrderId['newOrderId']));
                $withdrawals[] = $withdrawal;
            } catch (Exception $exception) {
                $errors[$index][] = $exception->getMessage();
            }
        }
        
        if (count($errors) > 0) {
            return $response->withStatus(422)->withJson(['errors' => $errors]);
        }
        
        $this->writeService->storeWithdrawals(...$withdrawals);
        
        return $response->withStatus(204);
    }
}