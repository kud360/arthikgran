<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('hello');View::make('hello');
});

Route::get('/phpinfo', function()
{
        phpinfo();
	return 1;
});

Route::get('/quick/single', 'QuickController@single');

Route::post('/quick/single', 'QuickController@parseSingle');