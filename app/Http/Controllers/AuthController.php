<?php
namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
//Validator
use App\Http\Requests\MedicalRegister;
use App\Http\Requests\MedicalLogin;

class AuthController extends Controller
{   
    //의료진 회원가입 
    public function register(MedicalRegister $request) {   
        //유효성 검사 (request 클래스 변경)
        $request->validated();
        User::create($request->except('password_confirmation'));    
        return response()->json(['sstatus' => 'success'], 200);
    }
    
    //의료진 및 관리자 로그인 (role : 1 => 관리자, 2 => 의료진)
    public function login(MedicalLogin $request) {  
        $request->validated();
        $credentials = $request->only('email', 'password');    
        if ($token = auth()->guard()->attempt($credentials)) {
            $user = Auth::user();
            // token header response 변경?  
            return response()->json(['status' => 'success', 'token' =>  $token, 'role' => $user->role ], 200);
            // ->header('Authorization', $token);
        }
        //로그인 실패 시 
        return response()->json(['error' => 'login_error'], 401) ;
    }

    public function logout() {
        $this->guard()->logout();
        return response()->json([
            'status' => 'success',
            'msg' => 'Logged out Successfully.'
        ], 200);
    }

    private function guard() {
        return Auth::guard();
    }
}