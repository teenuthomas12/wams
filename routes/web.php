<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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
$router->group(['prefix' => 'api', 'middleware' => 'jwt.auth'], function () use ($router) {
    $router->get('/work-allocation/dashboard', 'WorkAllocationController@dashboard');
    $router->get('/work-allocation', 'WorkAllocationController@index');
    $router->post('/work-allocation', 'WorkAllocationController@store');
    $router->get('/work-allocation/{id}', 'WorkAllocationController@show');
    $router->post('/work-allocation/update/{id}', 'WorkAllocationController@update');
    $router->delete('/work-allocation/{id}', 'WorkAllocationController@destroy');
    $router->get('/productivity/dashboard', 'ProductivityController@dashboard');
    $router->get('/productivity/list/open-productivities', 'ProductivityController@getOpenProductivities');
    $router->post('/productivity', 'ProductivityController@store');
    $router->get('/productivity', 'ProductivityController@index');
    $router->post('/productivity/update/{id}', 'ProductivityController@update');
});
