<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Auth::routes();
Route::get('/', 'LiveController@index');
Route::get('/dent', 'LiveController@dent');
Route::get('/sync', 'HomeController@sync');
Route::get('/home', 'HomeController@index')->name('index');

Route::get('{path}', 'HomeController@index')->where('path', '(.*)');
Route::get('{path}/{name}', 'HomeController@index')->where('path', '(.*)')->where('name', '(.*)');
