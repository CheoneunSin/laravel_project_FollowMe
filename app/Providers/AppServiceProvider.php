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
        $this->app->bind('ShortestPath', ShortestPath::class);        
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {   
        //환자 생성시 password 및 주민등록번호 암호화
        Patient::saving(function($user){
            if(!empty($user->password)){
                $user->password = bcrypt($user->password);
            }
            if(!empty($user->resident_number)){
                $user->resident_number = encrypt($user->resident_number);
            }
        });
        //의료진 회원가입 시 password 암호화
        User::saving(function($user){
            if(!empty($user->password)){
                $user->password = bcrypt($user->password);
            }
        });
        // //환자 값 가져올 시 주민등록번호 복호화 
        Patient::retrieved(function($user){     
            if(!empty($user->resident_number)){
                $user->resident_number = decrypt($user->resident_number);
            }
        });
    }
}
