<?php
/* --------------------------------------------------------------
   WithdrawalApiServiceProvider.php 2020-08-24
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Api\Modules\Withdrawal;

use Gambio\Admin\Modules\Withdrawal\Services\WithdrawalFactory;
use Gambio\Admin\Modules\Withdrawal\Services\WithdrawalFilterService;
use Gambio\Admin\Modules\Withdrawal\Services\WithdrawalReadService;
use Gambio\Admin\Modules\Withdrawal\Services\WithdrawalWriteService;
use Gambio\Api\Modules\Withdrawal\App\Actions\CreateWithdrawalsAction;
use Gambio\Api\Modules\Withdrawal\App\Actions\DeleteWithdrawalsAction;
use Gambio\Api\Modules\Withdrawal\App\Actions\FetchAllWithdrawalsAction;
use Gambio\Api\Modules\Withdrawal\App\Actions\FetchSpecificWithdrawalAction;
use Gambio\Api\Modules\Withdrawal\App\Actions\PatchWithdrawalsAction;
use Gambio\Api\Modules\Withdrawal\App\WithdrawalApiRequestParser;
use Gambio\Api\Modules\Withdrawal\App\WithdrawalApiRequestValidator;
use Gambio\Core\Application\DependencyInjection\AbstractServiceProvider;

/**
 * Class WithdrawalApiServiceProvider
 *
 * @package Gambio\Api\Modules\Withdrawal
 * @codeCoverageIgnore
 */
class WithdrawalApiServiceProvider extends AbstractServiceProvider
{
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            CreateWithdrawalsAction::class,
            DeleteWithdrawalsAction::class,
            FetchSpecificWithdrawalAction::class,
            FetchAllWithdrawalsAction::class,
            PatchWithdrawalsAction::class,
        ];
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->application->registerShared(WithdrawalApiRequestParser::class)->addArgument(WithdrawalFactory::class);
        
        $this->application->registerShared(WithdrawalApiRequestValidator::class);
        
        $this->application->registerShared(CreateWithdrawalsAction::class)
            ->addArgument(WithdrawalApiRequestParser::class)
            ->addArgument(WithdrawalApiRequestValidator::class)
            ->addArgument(WithdrawalWriteService::class);
        
        $this->application->registerShared(DeleteWithdrawalsAction::class)->addArgument(WithdrawalWriteService::class);
        
        $this->application->registerShared(FetchSpecificWithdrawalAction::class)->addArgument(WithdrawalReadService::class);
        
        $this->application->registerShared(FetchAllWithdrawalsAction::class)
            ->addArgument(WithdrawalApiRequestParser::class)
            ->addArgument(WithdrawalFilterService::class);
        
        $this->application->registerShared(PatchWithdrawalsAction::class)
            ->addArgument(WithdrawalApiRequestValidator::class)
            ->addArgument(WithdrawalApiRequestParser::class)
            ->addArgument(WithdrawalWriteService::class)
            ->addArgument(WithdrawalReadService::class)
            ->addArgument(WithdrawalFactory::class);
    }
}