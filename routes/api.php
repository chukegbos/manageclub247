<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
	
	Route::get('item/{id}', 'API\InventoryController@item');
	Route::get('setting', 'API\UserController@setting');
	Route::get('all', 'API\UserController@all');
	Route::put('setting/{id}', 'API\UserController@updateSetting');
	Route::get('stat', 'API\DashboardController@stat');
	Route::get('searchcustomer', 'API\CustomerController@searchcustomer');
	Route::get('allusers', 'API\UserController@allusers');
	Route::get('loadLGA/{id}', 'API\UserController@loadLGA');
	Route::get('members', 'LiveController@members');
	Route::get('ips', 'LiveController@apis');

	Route::group(['prefix' => 'user'], function(){
		Route::group(['prefix' => 'dashboard'], function(){
	    	Route::get('rep', 'API\DashboardController@rep');
	    	Route::get('sales', 'API\DashboardController@totalSales');
		});

		Route::get('login', 'API\UserController@logout');
		Route::get('relogin/{id}', 'API\UserController@relogin');
		Route::post('login', 'API\UserController@login');
		Route::get('bank', 'API\UserController@bank');
		Route::post('createpin', 'API\UserController@createpin');
	    Route::post('fetchbank', 'API\UserController@fetchbank');
	    Route::put('password', 'API\UserController@password');
	    Route::get('/', 'API\UserController@getUser');
	    Route::get('{id}', 'API\UserController@viewUser');

	    Route::post('credituser', 'API\UserController@credituser');
	    Route::post('walletuser', 'API\UserController@walletuser');
	});

	Route::group(['prefix' => 'cart'], function(){
    	Route::get('shopping/{sale_id}', 'API\CartController@testqty');
    	Route::get('testqty', 'API\CartController@testqty');
    	Route::get('getorder/{id}', 'API\DashboardController@getorder');
    	Route::post('setqty', 'API\CartController@setqty');
    	Route::get('addCart', 'API\CartController@addToCart');
    	Route::get('addCart/{id}', 'API\CartController@addCart');
    	Route::get('getCart', 'API\CartController@getCart');
    	Route::get('addOne/{id}', 'API\CartController@addOne');
    	Route::get('reduceOne/{id}', 'API\CartController@reduceOne');
    	Route::get('reduceAll/{id}', 'API\CartController@reduceAll');
    	Route::post('checkout', 'API\CartController@checkout');
    	Route::put('checkout/{id}', 'API\CartController@updateCheckout');
    	Route::post('approvequote', 'API\CartController@approvequote');
    	Route::post('closedeal', 'API\CartController@closedeal');
    	Route::get('cancel', 'API\CartController@cancel');
	});

	Route::group(['prefix' => 'purchases'], function(){
    	Route::get('/', 'API\PurchaseController@index');
        Route::get('all', 'API\PurchaseController@allindex');
        Route::get('reject/{purchase_id}', 'API\PurchaseController@reject');
        Route::get('approve/{purchase_id}', 'API\PurchaseController@approve');
        Route::get('accept/{purchase_id}', 'API\PurchaseController@accept');
    	Route::post('/', 'API\PurchaseController@store');
    	Route::post('non', 'API\PurchaseController@storenon');
    	Route::put('{id}', 'API\PurchaseController@update');
    	Route::get('{id}', 'API\PurchaseController@show');
    	Route::delete('{id}', 'API\PurchaseController@destroy');
	});
	
	Route::group(['prefix' => 'messages'], function(){
    	Route::get('/', 'API\MessageController@index');
    	Route::post('/', 'API\MessageController@store');
	});

    Route::group(['prefix' => 'store'], function(){	
    	Route::post('gettotal', 'API\StoreController@gettotal');
    	Route::get('makerequest', 'API\StoreRequestController@makerequest');
    	Route::get('getrequest/{req_id}', 'API\StoreRequestController@getrequest');
    	Route::get('viewrequest/{req_id}', 'API\StoreRequestController@viewrequest');
    	Route::get('request', 'API\StoreRequestController@index');
    	Route::get('getnumber', 'API\StoreController@getnumber');
    	Route::get('delete', 'API\StoreController@destroy');
    	Route::get('loadinventory', 'API\StoreController@loadinventory');
    	Route::delete('request/{id}', 'API\StoreRequestController@destroy');
    	Route::put('request/{id}', 'API\StoreRequestController@edit');
    	Route::post('/accept', 'API\StoreController@acceptall');
    	Route::get('/accept/{id}', 'API\StoreController@accept');
    	Route::get('/inventory', 'API\StoreController@getInventory');
    	Route::get('/tradeinventory', 'API\StoreController@tradeinventory');
    	
    	Route::get('/allinventory', 'API\StoreController@allInventory');
    	Route::get('/reports', 'API\StoreController@reports');
    	Route::get('/orders', 'API\StoreController@orders');
    	Route::get('/quotes', 'API\StoreController@quotes');
    	Route::get('/invoice', 'API\StoreController@invoice');
    	Route::get('/debtors', 'API\StoreController@debtors');
    	Route::post('/debt', 'API\StoreController@addDebt');
    	Route::get('/debtors/{sale_id}', 'API\StoreController@debtorview');
    	Route::post('/debtors', 'API\StoreController@storedebtors');

    	Route::get('{code}', 'API\StoreController@getStore');
    	Route::get('{code}/{id}', 'API\StoreController@show');

    	Route::get('room/{code}', 'API\StoreController@getRoom');
    	Route::get('room/{code}/{id}', 'API\StoreController@showroom');
	});

    Route::group(['prefix' => 'movement'], function(){
    	Route::post('', 'API\StoreController@updatemovement');
    	Route::post('req', 'API\StoreController@updatereq');
    	Route::get('/store', 'API\StoreController@storemovement');
    	Route::get('/movement', 'API\StoreController@mymovement');
    	Route::get('/request', 'API\StoreController@myrequest');
    	Route::get('/allmovement', 'API\StoreController@allmovement');
    	Route::get('/allrequest', 'API\StoreController@allrequest');
    	Route::get('/store/reject', 'API\StoreController@srejectall');
    	Route::get('/store/accept', 'API\StoreController@sacceptall');
    	Route::get('/store/delete', 'API\StoreController@reqdeleteall');
    	Route::get('/outlet/reject', 'API\StoreController@outletrejectall');
    	Route::get('/outlet/accept', 'API\StoreController@outletacceptall');
    	Route::get('initiate', 'API\StoreController@initiate');
    	Route::get('requestinitiate', 'API\StoreController@requestinitiate');
    	Route::get('{ref_id}', 'API\StoreController@getmovements');
	});

	Route::group(['prefix' => 'cashier'], function(){
	    Route::get('', 'API\SalesRepController@index');
	    Route::post('', 'API\SalesRepController@store');
	    Route::put('{id}', 'API\SalesRepController@update');
	    Route::delete('{id}', 'API\SalesRepController@destroy');
    });

    Route::group(['prefix' => 'customer'], function(){
	    Route::get('/', 'API\CustomerController@index');
	    Route::get('/details', 'API\CustomerController@details');
	    Route::get('/delete', 'API\CustomerController@delete');
	    Route::get('/edit/{id}', 'API\CustomerController@edit');
	    Route::get('/late/{unique_id}', 'API\CustomerController@late');
	    Route::get('/approve/{unique_id}', 'API\CustomerController@approve');
	    Route::get('{unique_id}', 'API\CustomerController@view');
	    Route::post('suspend', 'API\CustomerController@suspend');
	    Route::post('unsuspend', 'API\CustomerController@unsuspend');
	    Route::post('', 'API\CustomerController@store');
	    Route::put('{id}', 'API\CustomerController@update');
	    Route::delete('{id}', 'API\CustomerController@destroy');
    });

    Route::group(['prefix' => 'admin'], function(){
	    Route::get('', 'API\UserController@index');
	    Route::post('', 'API\UserController@store');
	    Route::post('settrequest', 'API\StoreController@storereq');
	    Route::post('updaterequest', 'API\StoreController@updatereq');
	    Route::get('delete', 'API\UserController@destroyall');
	    Route::put('{id}', 'API\UserController@update');
	    Route::delete('{id}', 'API\UserController@destroy');
	    Route::get('inventory', 'API\InventoryController@getInventory');
	    Route::get('sales', 'API\DashboardController@orders');

	    Route::get('logins', 'API\DashboardController@logins');
	    Route::get('logins/{id}', 'API\DashboardController@approvelogin');
    	Route::get('/order-report', 'API\DashboardController@orderReport');
    	Route::get('/discharge', 'API\StoreController@discharge');
    	Route::put('/store/{id}', 'API\StoreController@storeBar');
    	Route::put('/role/{id}', 'API\UserController@storeRole');
    	Route::put('/discharge/{id}', 'API\UserController@approve');
    	Route::get('/discharge/{id}', 'API\StoreController@approve');
    	Route::get('/decline/{id}', 'API\StoreController@decline');
    });

    //Bar manager route
    Route::group(['prefix' => 'manager'], function(){
	    Route::get('', 'API\ManagerController@index');
	    Route::post('', 'API\ManagerController@store');
	    Route::put('{id}', 'API\ManagerController@update');
	    Route::delete('{id}', 'API\ManagerController@destroy');
    });

    /** Inventory Route */
	Route::group(['prefix' => 'inventory'], function(){
		Route::group(['prefix' => 'category'], function(){
		    Route::get('', 'API\CategoryController@index');
		    Route::get('delete', 'API\CategoryController@destroy');
		    Route::get('{id}', 'API\CategoryController@show');
		    Route::post('', 'API\CategoryController@store');
		    Route::put('{id}', 'API\CategoryController@update');
		    Route::delete('{id}', 'API\CategoryController@destroy');
	    });

	    Route::get('', 'API\InventoryController@index');
	    Route::post('increase', 'API\InventoryController@increase');
	    Route::get('reset', 'API\InventoryController@reset');
	    Route::get('store/reset', 'API\InventoryController@storereset');
	    Route::get('delete', 'API\InventoryController@destroy');
	    Route::get('{id}', 'API\InventoryController@show');
	    Route::post('', 'API\InventoryController@store');
	    Route::put('{id}', 'API\InventoryController@update');
	    
	    Route::delete('{id}', 'API\InventoryController@destroy');
    });
   
    Route::apiResources(['store' => 'API\StoreController']);

    /** Account Route */
	Route::group(['prefix' => 'account'], function(){
	    Route::get('', 'API\AccountController@index');
	    Route::get('search', 'API\AccountController@search');
	    Route::get('trialbalance', 'API\AccountController@trialbalance');
	    Route::get('profitloss', 'API\AccountController@profitloss');
	    Route::get('balancesheet', 'API\AccountController@balancesheet');
	    Route::get('{id}', 'API\AccountController@show');
	    Route::post('', 'API\AccountController@store');
	    Route::put('{id}', 'API\AccountController@update');
    });

    Route::group(['prefix' => 'type'], function(){
	    Route::get('', 'API\AccountTypeController@index');
	    Route::get('/search', 'API\AccountTypeController@search');
	    Route::get('{id}', 'API\AccountTypeController@show');
	    Route::post('', 'API\AccountTypeController@store');
	    Route::put('{id}', 'API\AccountTypeController@update');
	    Route::delete('{id}', 'API\AccountTypeController@destroy');
    });

    Route::group(['prefix' => 'tax'], function(){
	    Route::get('', 'API\AccountTaxController@index');
	    Route::get('/search', 'API\AccountTaxController@search');
	    Route::get('{id}', 'API\AccountTaxController@show');
	    Route::post('', 'API\AccountTaxController@store');
	    Route::put('{id}', 'API\AccountTaxController@update');
	    Route::delete('{id}', 'API\AccountTaxController@destroy');
    });

    Route::group(['prefix' => 'ledger'], function(){
	    Route::get('', 'API\LedgerController@index');
	    Route::get('/search', 'API\LedgerController@search');
	    Route::get('{id}', 'API\LedgerController@show');
	    Route::post('', 'API\LedgerController@store');
	    Route::put('{id}', 'API\LedgerController@update');
	    Route::delete('{id}', 'API\LedgerController@destroy');
    });

    Route::group(['prefix' => 'funding'], function(){
	    Route::get('', 'API\UserController@funding');
	    Route::get('approve/{ref_id}', 'API\UserController@approvefund');
	    Route::get('reject/{ref_id}', 'API\UserController@rejectfund');
	   	Route::get('{ref_id}', 'API\UserController@fundreceipt');
    });

    Route::group(['prefix' => 'suppliers'], function(){
    	Route::get('/', 'API\SupplierController@index');
    	Route::get('delete', 'API\SupplierController@destroy');
    	Route::get('/debtors', 'API\SupplierController@debtors');
    	Route::post('/debtors', 'API\SupplierController@storedebtors');
    	Route::get('all', 'API\SupplierController@allSuppliers');
    	Route::get('{id}', 'API\SupplierController@singleSupplier');
    	Route::post('/', 'API\SupplierController@store');
    	Route::put('{id}', 'API\SupplierController@update');
        Route::delete('{id}', 'API\SupplierController@destroy');
    });


    Route::group(['prefix' => 'payment'], function(){	
    	Route::group(['prefix' => 'method'], function(){
	    	Route::get('/', 'API\PaymentController@method');
	    	Route::post('/', 'API\PaymentController@storemethod');
	    	Route::put('{id}', 'API\PaymentController@updatemethod');
		});

		Route::group(['prefix' => 'channels'], function(){
	    	Route::get('/', 'API\PaymentController@channel');
	    	Route::post('/', 'API\PaymentController@storechannel');
	    	Route::put('{id}', 'API\PaymentController@updatechannel');
	    	Route::get('delete', 'API\PaymentController@destroychannel');
		});

		Route::group(['prefix' => 'banks'], function(){
	    	Route::get('/', 'API\PaymentController@bank');
	    	Route::post('/', 'API\PaymentController@storebank');
	    	Route::put('{id}', 'API\PaymentController@updatebank');
	    	Route::get('delete', 'API\PaymentController@destroybank');
		});

		Route::group(['prefix' => 'pos'], function(){
	    	Route::get('/', 'API\PaymentController@pos');
	    	Route::post('/', 'API\PaymentController@storepos');
	    	Route::put('{id}', 'API\PaymentController@updatepos');
	    	Route::get('delete', 'API\PaymentController@destroypos');
		});

		Route::group(['prefix' => 'debits'], function(){	
	    	Route::get('/', 'API\PaymentController@debit');
	    	Route::get('member/{member_id}', 'API\PaymentController@getmember');
	    	Route::post('/', 'API\PaymentController@storedebit');
	    	Route::post('pay', 'API\PaymentController@storedebitpay');
	    	Route::put('{id}', 'API\PaymentController@updatedebit');
	    	Route::get('delete', 'API\PaymentController@destroydebit');
		});

		Route::get('', 'API\PaymentController@index');
		Route::post('debit', 'API\PaymentController@debitmembers');
		Route::post('graceperiod', 'API\PaymentController@graceperiod');
		Route::post('pay', 'API\PaymentController@pay');
	});

	Route::group(['prefix' => 'members'], function(){	
    	Route::group(['prefix' => 'types'], function(){	
	    	Route::get('/', 'API\CustomerController@membertype');
	    	Route::post('/', 'API\CustomerController@storemembertype');
	    	Route::put('{id}', 'API\CustomerController@updatemembertype');
	    	Route::get('delete', 'API\CustomerController@destroymembertype');
		});

		Route::group(['prefix' => 'sections'], function(){	
	    	Route::get('/', 'API\CustomerController@membersection');
	    	Route::post('/', 'API\CustomerController@storemembersection');
	    	Route::put('{id}', 'API\CustomerController@updatemembersection');
	    	Route::get('delete', 'API\CustomerController@destroymembersection');
		});
	});