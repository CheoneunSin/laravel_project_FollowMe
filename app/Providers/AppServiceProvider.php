<?php

namespace App\Providers;
use App\testPatient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Dijkstra', Dijkstra::class);
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        testPatient::saving(function($user){
            if(!empty($user->login_pw)){
                $user->login_pw = \Crypt::encrypt($user->login_pw);
            }
        });

        // testPatient::retrieved(function($user){
        //     if(!empty($user->login_pw)){
        //         $user->login_pw = \Crypt::decrypt($user->login_pw);
        //     }
        // });
    }
}
