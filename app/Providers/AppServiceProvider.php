<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\User;
use View;
use App\Providers;
use App\Setting;
use App\Debtor;
use App\Store;
use App\Kitchen;
use App\Notification;
use App\Sale;
use Auth;
use DB;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        View::composer('*', function($view)
        {
            $view->with('setting', Setting::find(1));

            if (Auth::guard('web')->check()) {
                $role = Auth()->user()->role;
                $store_id = Auth()->user()->getOriginal('store');
                $view->with('my_store', Store::where('deleted_at', NULL)->find($store_id));

                $kitchen_id = Auth()->user()->getOriginal('kitchen');
                $view->with('my_kitchen', Kitchen::where('deleted_at', NULL)->find($kitchen_id));
            }
        });
    }
}
