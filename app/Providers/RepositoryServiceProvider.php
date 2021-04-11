<?php

namespace App\Providers;

use App\Api\V1\Repositories\Eloquent\AdsConditionElementsEloquentRepository;
use App\Api\V1\Repositories\Eloquent\AdsConditionTypesEloquentRepository;
use App\Api\V1\Repositories\Eloquent\AdsCounterpartyConditionsEloquentRepository;
use App\Api\V1\Repositories\Eloquent\AdsEloquentRepository;
use App\Api\V1\Repositories\Eloquent\AdsPaymentMethodEloquentRepository;
use App\Api\V1\Repositories\Eloquent\AdsPaymentTimeLimitEloquentRepository;
use App\Api\V1\Repositories\Eloquent\EscrowEloquentRepository;
use App\Api\V1\Repositories\Eloquent\EscrowReleaseEloquentRepository;
use App\Api\V1\Repositories\Eloquent\OrderCancelledEloquentRepository;
use App\Api\V1\Repositories\Eloquent\OrderChatEloquentRepository;
use App\Api\V1\Repositories\Eloquent\OrdersEloquentRepository;
use App\Api\V1\Repositories\Eloquent\PaymentMethodsEloquentRepository;
use App\Contracts\Repository\IUserRepository;
use App\Api\V1\Repositories\Eloquent\SysActivityTypesEloquentRepository;
use App\Api\V1\Repositories\Eloquent\SysCurrencyEloquentRepository;
use App\Api\V1\Repositories\Eloquent\SysCurrencyTypesEloquentRepository;
use App\Api\V1\Repositories\Eloquent\SysSettingsEloquentRepository;
use App\Api\V1\Repositories\Eloquent\SysWalletCashflowChannelsEloquentRepository;
use App\Api\V1\Repositories\Eloquent\SysWalletTypesEloquentRepository;
use App\Api\V1\Repositories\Eloquent\UserEloquentRepository;
use App\Api\V1\Repositories\Eloquent\UserPaymentAccountDetailEloquentRepository;
use App\Api\V1\Repositories\Eloquent\UserRoleEloquentRepository;
use App\Api\V1\Repositories\Eloquent\UserVerificationEloquentRepository;
use App\Api\V1\Repositories\Eloquent\WalletP2PCreditLogEloquentRepository;
use App\Api\V1\Repositories\Eloquent\WalletP2PDebitLogEloquentRepository;
use App\Api\V1\Repositories\Eloquent\WalletP2PEloquentRepository;
use App\Api\V1\Repositories\Eloquent\WalletSpotEloquentRepository;
use App\Api\V1\Repositories\Eloquent\WalletTypesEloquentRepository;
use App\Contracts\Repository\IAdsConditionElementsRepository;
use App\Contracts\Repository\IAdsConditionTypesRepository;
use App\Contracts\Repository\IAdsCounterpartyConditionsRepository;
use App\Contracts\Repository\IAdsPaymentMethodRepository;
use App\Contracts\Repository\IAdsPaymentTimeLimitRepository;
use App\Contracts\Repository\IAdsRepository;
use App\Contracts\Repository\IEscrowReleaseRepository;
use App\Contracts\Repository\IEscrowRepository;
use App\Contracts\Repository\IOrderCancelledRepository;
use App\Contracts\Repository\IOrderChatRepository;
use App\Contracts\Repository\IOrdersRepository;
use App\Contracts\Repository\IPaymentMethodsRepository;
use App\Contracts\Repository\ISysActivityTypesRepository;
use App\Contracts\Repository\ISysCurrencyRepository;
use App\Contracts\Repository\ISysCurrencyTypesRepository;
use App\Contracts\Repository\ISysSettingsRepository;
use App\Contracts\Repository\ISysWalletCashflowChannelsRepository;
use App\Contracts\Repository\ISysWalletTypesRepository;
use App\Contracts\Repository\IUserPaymentAccountDetail;
use App\Contracts\Repository\IUserRoleRepository;
use App\Contracts\Repository\IUserVerificationRepository;
use App\Contracts\Repository\IWalletP2PCreditLogRepository;
use App\Contracts\Repository\IWalletP2PDebitLogRepository;
use App\Contracts\Repository\IWalletP2PRepository;
use App\Contracts\Repository\IWalletSpotRepository;
use App\Contracts\Repository\IWalletTypesRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //Repositories

        $this->app->bind(IUserRepository::class, UserEloquentRepository::class);
        $this->app->bind(IUserPaymentAccountDetail::class, UserPaymentAccountDetailEloquentRepository::class);
        $this->app->bind(IAdsRepository::class, AdsEloquentRepository::class);
        $this->app->bind(IAdsConditionElementsRepository::class, AdsConditionElementsEloquentRepository::class);
        $this->app->bind(IAdsConditionTypesRepository::class, AdsConditionTypesEloquentRepository::class);
        $this->app->bind(IAdsCounterpartyConditionsRepository::class, AdsCounterpartyConditionsEloquentRepository::class);
        $this->app->bind(IAdsPaymentMethodRepository::class, AdsPaymentMethodEloquentRepository::class);
        $this->app->bind(IAdsPaymentTimeLimitRepository::class, AdsPaymentTimeLimitEloquentRepository::class);
        $this->app->bind(IEscrowRepository::class, EscrowEloquentRepository::class);
        $this->app->bind(IEscrowReleaseRepository::class, EscrowReleaseEloquentRepository::class);
        $this->app->bind(IOrdersRepository::class, OrdersEloquentRepository::class);
        $this->app->bind(IOrderCancelledRepository::class, OrderCancelledEloquentRepository::class);
        $this->app->bind(IOrderChatRepository::class, OrderChatEloquentRepository::class);
        $this->app->bind(IPaymentMethodsRepository::class, PaymentMethodsEloquentRepository::class);
        $this->app->bind(ISysActivityTypesRepository::class, SysActivityTypesEloquentRepository::class);
        $this->app->bind(ISysCurrencyRepository::class, SysCurrencyEloquentRepository::class);
        $this->app->bind(ISysCurrencyTypesRepository::class, SysCurrencyTypesEloquentRepository::class);
        $this->app->bind(ISysSettingsRepository::class, SysSettingsEloquentRepository::class);
        $this->app->bind(ISysWalletCashflowChannelsRepository::class, SysWalletCashflowChannelsEloquentRepository::class);
        $this->app->bind(ISysWalletTypesRepository::class, SysWalletTypesEloquentRepository::class);
        $this->app->bind(IUserRoleRepository::class, UserRoleEloquentRepository::class);
        $this->app->bind(IUserVerificationRepository::class, UserVerificationEloquentRepository::class);
        $this->app->bind(IWalletP2PRepository::class, WalletP2PEloquentRepository::class);
        $this->app->bind(IWalletP2PCreditLogRepository::class, WalletP2PCreditLogEloquentRepository::class);
        $this->app->bind(IWalletP2PDebitLogRepository::class, WalletP2PDebitLogEloquentRepository::class);
        $this->app->bind(IWalletSpotRepository::class, WalletSpotEloquentRepository::class);
        $this->app->bind(IWalletTypesRepository::class, WalletTypesEloquentRepository::class);
    }
}
