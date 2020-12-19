<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }


    // public function vue_login(Request $request)
    // {
    //     $request->session()->put('test', 'value');
    //     $value = $request->session()->get('test');
    //     return $value;
    // }

    // public function vue_main(Request $request)
    // {
    //     $value = $request->session()->get('test');
    //     return $value;
    // }

    // public function vue_logout(Request $request)
    // {
    //     $request->session()->forget('test');
    //     return "logout";
    // }
}
