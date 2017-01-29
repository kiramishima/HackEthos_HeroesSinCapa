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

$app->get('/', function () use ($app) {
    return $app->version();
});

$app->group(['prefix' => '/api/v1'], function () use ($app) {
    /** Usuarios **/
    $app->post('/sign-in','UserController@login');
    $app->post('/sign-up','UserController@signup');
    $app->get('/profile', 'UserController@profile');
    $app->post('/posts', 'PostController@add_Post');
    $app->get('/posts', 'PostController@list_posts');
    // $app->get('/edificios','EdificioController@listEdificios');
});
