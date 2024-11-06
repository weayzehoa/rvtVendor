<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;
use \App\Http\ViewComposers\AdminIndexComposer;
use \App\Http\ViewComposers\VendorIndexComposer;
use \App\Http\ViewComposers\GateIndexComposer;
use Illuminate\Pagination\Paginator;

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
        //註冊自定義帳號資料表的密碼檢驗規則
        \Auth::provider('self-eloquent', function ($app, $config) {
            return New \App\Libs\SelfEloquentProvider($app['hash'], $config['model']);
        });

        //使用 Bootstrap 分頁樣式
        Paginator::useBootstrap();

        //註冊VendorIndexComposer 視圖共用變數給商家後台用 vendor.* 所有視圖
        //這樣可以給被分離出去的 menu.blade 或 footer.blade 這些視圖共同使用
        view()->composer('vendor.*', VendorIndexComposer::class); //舊版
        view()->composer('*', VendorIndexComposer::class); //新版

        //只要有作紀錄動作，將IP寫入特定的紀錄IP欄位
        Activity::saving(function(Activity $activity) { $activity->ip = $activity->ip = request()->ip();});
    }
}
