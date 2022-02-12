<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/



$router->get('/', function () use ($router) {
    return $router->app->version();
});

$api = app('Dingo\Api\Routing\Router');
$api->version(
    'v1',
    [
        'namespace' => 'App\Api\V1\Controllers'
    ],
    function ($api) {



        $api->group(['middleware' => ['auth:api', 'scopes:user']], function ($api) {

            /**
             * Users Route
             */

            $api->get('user', [
                'as' => 'authorization.user',
                'uses' => 'UserController@findAll',
            ]);

            $api->get('user/{id}', [
                'as' => 'authorization.show',
                'uses' => 'UserController@find',
            ]);

            /**
             * Ads Route
             */
            $api->get('ads', [
                'as' => 'ads.findall',
                'uses' => 'AdsController@findAll',
            ]);

            $api->get('ads/{id}', [
                'as' => 'ads.find',
                'uses' => 'AdsController@findOne',
            ]);

            $api->get('ads_page_data', [
                'as' => 'ads.pagedata',
                'uses' => 'AdsController@pageData',
            ]);

            $api->post('ads', [
                'as' => 'ads.create',
                'uses' => 'AdsController@create',
            ]);
            $api->put('ads/visit', [
                'as' => 'ads.visit',
                'uses' => 'AdsController@updateVisit',
            ]);


            /**
             * Ads Visit Route
             */
            $api->get('ads_visit', [
                'as' => 'ads_visit.findall',
                'uses' => 'AdsVisitController@findAll',
            ]);

            $api->get('ads_visit/{id}', [
                'as' => 'ads_visit.find',
                'uses' => 'AdsVisitController@findOne',
            ]);

            $api->post('ads_visit/{id}', [
                'as' => 'ads_visit.create',
                'uses' => 'AdsVisitController@create',
            ]);


            /**
             * Asset Route
             */
            $api->post('wallet/deposit/network', [
                'as' => 'wallet.deposit_network',
                'uses' => 'AssetDepositController@networkDeposit',
            ]);


            /**
             * Orders Route
             */
            $api->get('orders', [
                'as' => 'orders.findall',
                'uses' => 'OrderController@findAll',
            ]);

            $api->get('orders/{id}', [
                'as' => 'orders.find',
                'uses' => 'OrderController@findOne',
            ]);

            $api->post('orders', [
                'as' => 'orders.create',
                'uses' => 'OrderController@create',
            ]);


            $api->delete('orders/{orderID}/cancel', [
                'as' => 'orders.cancel',
                'uses' => 'OrderController@cancelOrder',
            ]);

            $api->put('orders/{orderID}/buyer_confirm', [
                'as' => 'orders.buyer_confirm',
                'uses' => 'OrderController@buyerConfirm',
            ]);

            $api->put('orders/{orderID}/seller_confirm', [
                'as' => 'orders.seller_confirm',
                'uses' => 'OrderController@sellerConfirm',
            ]);
            /**
             * Payment Methods Route
             */
            $api->get('payment_methods', [
                'as' => 'payment_methods',
                'uses' => 'PaymentMethodsController@findAll',
            ]);

            $api->get('payment_methods/{id}', [
                'as' => 'payment_methods.find',
                'uses' => 'PaymentMethodsController@find',
            ]);
        });



        /**
         * Auth route
         */
        $api->post('user', [
            'as' => 'authorizations.register',
            'uses' => 'UserController@register',
        ]);

        $api->post('login', [
            'as' => 'authorization.login',
            'uses' => 'UserController@login',
        ]);
    }
);
