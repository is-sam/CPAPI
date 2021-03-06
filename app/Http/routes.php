<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('API', function () {
    return view('welcome');
});
Route::post('API/access_token', function(){
    return Response::json(Authorizer::issueAccessToken());
});
Route::group(['prefix' => 'API/{lang}', 'where' => ['lang' => 'fr|en'], 'middleware' => 'oauth'], function() {
    Route::resource('/commodities', 'APIController', ['only' => ['index', 'show']]);
});

// Authentication routes...
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::get('auth/logout', 'Auth\AuthController@getLogout');

// Registration routes...
Route::get('auth/register', 'Auth\AuthController@getRegister');
Route::post('auth/register', 'Auth\AuthController@postRegister');
