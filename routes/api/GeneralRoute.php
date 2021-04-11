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
