<?php

namespace App\Providers;
use App\Patient;
use App\User;

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
        Patient::saving(function($user){
            if(!empty($user->password)){
                $user->password = bcrypt($user->password);
            }
            if(!empty($user->resident_number)){
                $user->resident_number = encrypt($user->resident_number);
            }
        });
        User::saving(function($user){
            if(!empty($user->password)){
                $user->password = bcrypt($user->password);
            }
        });
        // //사용자 비밀번호 전부 암호화 후 사용
        Patient::retrieved(function($user){     
            if(!empty($user->resident_number)){
                $user->resident_number = decrypt($user->resident_number);
            }
        });
    }
}
