<?php

use Illuminate\Support\Facades\Route;

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

//Home page
Route::get('/', 'IndexController@index');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

//Category/listing page
Route::get('/products/{url}', 'ProductsController@products');

//Product details page
Route::get('/products/{id}', 'ProductsController@product');


//Admin page
Route::match(['get', 'post'], '/admin', 'AdminController@login');
Route::group(['middleware'=> ['auth']], function(){
    Route::get('/admin/dashboard', 'AdminController@dashboard');
    Route::get('/admin/settings', 'AdminController@settings');
    Route::get('/admin/check-pwd', 'AdminController@checkPassword');
    Route::match(['get', 'post'], '/admin/update-pwd', 'AdminController@updatePassword');
    

    //categories route (admin)
    Route::match(['get', 'post'], '/admin/add-category', 'CategoryController@addCategory');
    Route::match(['get', 'post'], '/admin/edit-category/{id}', 'CategoryController@editCategory');
    Route::match(['get', 'post'], '/admin/delete-category/{id}', 'CategoryController@deleteCategory');
    Route::get('/admin/view-categories', 'CategoryController@viewCategories');

    //products route(admin)
    Route::match(['get', 'post'], '/admin/add-products', 'ProductsController@addProducts');
    Route::match(['get', 'post'], '/admin/edit-product/{id}', 'ProductsController@editProducts');
    Route::get('/admin/view-products', 'ProductsController@viewProducts');
    Route::get('/admin/delete-product/{id}', 'ProductsController@deleteProduct');

});

Route::get('/logout', 'AdminController@logout');


