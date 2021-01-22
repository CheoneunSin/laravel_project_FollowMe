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
        // testPatient::saving(function($user){
        //     if(!empty($user->login_pw)){
        //         $user->login_pw = bcrypt($user->login_pw);
        //     }
        // });
        // //사용자 비밀번호 전부 암호화 후 사용
        // testPatient::retrieved(function($user){     
        //     if(!empty($user->login_pw)){
        //         $user->login_pw = \Crypt::decrypt($user->login_pw);
        //     }
        // });
    }
}
