<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/**
 * API VERSION: 1
 */
$API_VERSION = 1;

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// routes with proper JSON API Specifications format - without authentication
$router->group(['middleware' => ['json.api.headers'], 'prefix' => 'api/v' . $API_VERSION], function () use ($router) {

    // ************************************************
    // Login and registration
    // ************************************************
    $router->group(['namespace' => 'Users'], function () use ($router) {
        // register
        $router->post('/users/register', [
            'uses' => 'UsersController@register',
            'as' => 'users.register'
        ]);
        // login
        $router->post('/users/login', [
            'uses' => 'UsersController@login',
            'as' => 'users.login'
        ]);
    });
});

// routes with proper JSON API Specifications format - requires authentication
$router->group(['middleware' => ['auth', 'json.api.headers'], 'prefix' => 'api/v' . $API_VERSION], function () use ($router) {

    // ************************************************
    // users
    // ************************************************
    $router->group(['namespace' => 'Users'], function () use ($router) {
        $router->get('/users/{user}', [
            'uses' => 'UsersController@show',
            'as' => 'users.show'
        ]);
        $router->get('/users', [
            'uses' => 'UsersController@index',
            'as' => 'users.index'
        ]);
        $router->patch('/users/{user}', [
            'uses' => 'UsersController@update',
            'as' => 'users.update'
        ]);
        $router->delete('/users/{user}', [
            'uses' => 'UsersController@destroy',
            'as' => 'users.destroy'
        ]);
        // logout
        $router->post('/users/logout', [
            'uses' => 'UsersController@logout',
            'as' => 'users.logout'
        ]);
    });
});
