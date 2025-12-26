<?php
/* --------------------------------------------------------------
   DeleteWithdrawalsAction.php 2020-08-24
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
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

/**
 * Class DeleteWithdrawalsAction
 *
 * @package Gambio\Api\Modules\Withdrawal\App\Actions
 */
class DeleteWithdrawalsAction
{
    /**
     * @var WithdrawalWriteService
     */
    private $service;
    
    
    /**
     * DeleteWithdrawalsAction constructor.
     *
     * @param WithdrawalWriteService $service
     */
    public function __construct(WithdrawalWriteService $service)
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
        $ids = [];
        if ($request->getAttribute('ids') !== null) {
            foreach (explode(',', $request->getAttribute('ids')) as $id) {
                $ids[] = (int)$id;
            }
        }
        
        $this->service->deleteWithdrawals(...$ids);
        
        return $response->withStatus(204);
    }
}