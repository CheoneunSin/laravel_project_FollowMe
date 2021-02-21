# 해야할 일 
- 노드 페이지 API   
- 디스플레이 용 API 작성 
- 메시지 변경 (Web 메시지 X, App 메시지 변경)
- 다익스트라 알고리즘 공부 
- 중복 배제- 코드 model로 이동 

# API 종류
## App
- 환자 로그인 
- 환자 회원가입 
- 환자 로그아웃 
- 의료진 앱 진료실 QR스캐너 -> 진료 접수
- 삼변측량에 필요한 노드, 비콘 정보 GET
- 진료 동선 안내 (다익스트라 알고리즘을 통한 최단 경로 노드)
- 검색을 통한 내비게이션 
- 대기순번 (pusher를 사용한 실시간 대기순번)
- 미결제 진료비 내역
- 결제된 진료비 내역 조회
- 이전 진료 동선 조회

## 관리자 Web
- 관리자 로그인
- 비콘 메인페이지 - 비콘 위치 및 정보 가져오기
- 비콘 정보 수정
- 비콘 정보 검색 
- 노드 메인페이지 - 노드 위치정보 + 노드 연결 정보 GET 
- 노드 정보 및 연결 정보 수정하기 

## 의료진 Web
- 의료진 로그인
- 의료진 회원가입 (관리자만 회원가입 가능 => 수정 필요 )
- 환자 데이터 생성 (앱 회원자인지 판별 후 create, update) 
- 환자 이름으로 검색 (동명이인 목록 반환)
- 환자 목록에서 선택 후 환자 정보 조회(환자 정보, 최근 진료 데이터, 진료 동선) 
- 환자 진료 데이터 업데이트 (QR코드 접수로 알 수 없는 데이터 : 의사 이름, 진료 시간, 진료비 등)
- 환자 현재 날짜 진료 데이터 가져오기 
- 해당 환자 진료 종료 버튼 클릭 시 대기순번에 반영
- 환자 진료 동선 설정 

# git 제외 파일
/node_modules
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.backup
.phpunit.result.cache
Homestead.json
Homestead.yaml
npm-debug.log
yarn-error.log

# eloquent 
- [참고사이트](https://silnex.github.io/blog/laravel-eloquent-tips-tricks/)
# 비콘 스캐너 SQL
```php
UPDATE `beacons` SET `beacon_scanner_id`= "1_S001" WHERE `beacon_id_minor` >= 15013 AND `beacon_id_minor` <= 15015;
UPDATE `beacons` SET `beacon_scanner_id`= "1_S002" WHERE `beacon_id_minor` >= 15001  AND `beacon_id_minor` <= 15003;
UPDATE `beacons` SET `beacon_scanner_id`= "2_S001" WHERE `beacon_id_minor` >= 15004  AND `beacon_id_minor` <= 15006;
UPDATE `beacons` SET `beacon_scanner_id`= "2_S002" WHERE `beacon_id_minor` >= 15007  AND `beacon_id_minor` <= 15009;
UPDATE `beacons` SET `beacon_scanner_id`= "2_S003" WHERE `beacon_id_minor` >= 15010  AND `beacon_id_minor` <= 15012;
```

# 전체 진료 동선 
```php
//진료 동선 안내 ( + 다익스트라 알고리즘)
public function app_flow(Request $request){
    //DB에 저장된 노드 정보(노드 간 연결 거리)를 배열에 저장
    $graph = []; 
    foreach (Node::select('node_id')->cursor() as $node) {
        $graph["$node->node_id"] = [];
        foreach (NodeDistance::cursor() as $distance) {
            //노드 간 거리 저장
            if($distance->node_A == $node->node_id){
                $graph["$node->node_id"]["$distance->node_B"] = $distance->distance;
            }
        }
    }
    $algorithm = new Dijkstra($graph);  //다익스타라 알고리즘 적용 
    //환자가 가야하는 동선 가져오기 
    $flows      = Auth::guard('patient')->user()->flow()->with('room_location')->where("flow_status_check", 1)->get();
    $start_node = [];
    $end_node   = [];
    $i          = 0;
    //전체 동선의 출발점 , 도착점 저장 
    foreach($flows as $flow){
        array_push($start_node, $flow->room_location->room_node);
        if($i === 0){
            $i = 1;
            continue;
        }
        array_push($end_node, $flow->room_location->room_node);
    }
    // return response()->json([
    //     'start_node' => $start_node,
    //     'end_node' => $end_node
    // ],200);
    //출발점 노드와 도착점 노드 사이의 최단 경로 가져오기 
    $paths = [];
    for($i = 0 ; $i < count($end_node) ; $i++){
        $path = $algorithm->shortestPaths($start_node[$i], $end_node[$i]); 
        array_push($paths, $path);
    }
    //최단 경로에 있는 노드들에 대한 정보를 DB에서 가져와서 nodeFlow 배열에 저장 
    $nodeFlows = [];
    for($i = 0 ; $i < count($paths) ; $i++){
        $path = implode(',', $paths[$i][0]); //WhereIn은 자동 sort되므로 implode 후 FIELD 해야함
        $nodeFlow = Node::whereIn('node_id', $paths[$i][0])->orderByRaw(DB::raw("FIELD(node_id, $path)"))->get();
        
        // $nodeFlow = Node::whereIn('node_id', $paths[$i][0])->get();
        array_push($nodeFlows, $nodeFlow);
    }
    //전체 진료 동선의 최단 경로 반환         
    return response()->json([
        'nodeFlow' => $nodeFlows,
    ],200);
    // [], JSON_PRETTY_PRINT
}
```


# WhereIn 정렬문제 
```php 
$temp = [22, 26, 20, 24];
$tempStr = implode(',', $temp);
$robjeks = DB::table('objek')
    ->whereIn('id', $temp)
    ->orderByRaw(DB::raw("FIELD(id, $tempStr)"))
    ->get();
```
# 이미지 업로드 

extension=php_fileinfo.dll

# JWT 인증 서비스 
- composer require tymon/jwt-auth
- php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"\
<br>
config/jwt.php 생김 

- php artisan jwt:secret
- .env에 파일 업데이트 -> JWT_SECRET=foobar

- 모델 업데이트 
```php
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

public function getJWTIdentifier()
{
    return $this->getKey();
}

public function getJWTCustomClaims()
{
    return [];
}
```
- config/auth.php
```php
'defaults' => [ 
    'guard' => 'api',
    'passwords' => 'users',
],

...

'guards' => [
    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
],
Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], 
```
- routes/api.php
```php

function ($router) {

    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');

});
```
- AuthController
```php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
```
- 승인된 헤더 
<br>Authorization: Bearer eyJhbGciOiJIUzI1NiI..

- config/jwt.php 
<br> 'secret' => env('JWT_SECRET'),

- composer require fruitcake / laravel-cors
- Kerner.php 
```php 
protected $middleware = [
        \Fruitcake\Cors\HandleCors::class,
    ];
```
- jwt.php 변경 
```php
        // 'jwt' => Tymon\JWTAuth\Providers\JWT\Lcobucci::class,
        'jwt' => Tymon\JWTAuth\Providers\JWT\Namshi::class,
```
[참고자료](https://www.youtube.com/watch?v=iQv2mdktmzE)
[참고자료2] (https://medium.com/@ripoche.b/create-a-spa-with-role-based-authentication-with-laravel-and-vue-js-ac4b260b882f)


# 2개의 테이블 Auth
- Illuminate\Foundation\Auth 
```php
namespace Illuminate\Foundation\Auth;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

    class Admin extends Model implements
        AuthenticatableContract,
        AuthorizableContract,
        CanResetPasswordContract
    {
        use Authenticatable, Authorizable, CanResetPassword;
    } 
```
- Authenticatable 확장 -> Admin 모델 
```php 
namespace App;
use Illuminate\Foundation\Auth\Admin as Authenticatable;

class Admin extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
}

```
- config/auth.php -> providers  변경 
```php 
'admins' => [
            'driver' => 'eloquent',
            'model' => App\Admin::class,
        ], 
```
- config/auth.php guards 변경 
```php 
 'user' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
 'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],
```

- 컨트롤러 
```php 
#user
if (Auth::guard('user')->attempt(['email' => $email, 'password' => $password])) {
    $details = Auth::guard('user')->user();
    $user = $details['original'];
    return $user;
} else {
    return 'auth fail';
}

#admin
if (Auth::guard('admin')->attempt(['email' => $email, 'password' => $password])) {
    $details = Auth::guard('admin')->user();
    $user = $details['original'];
    return $user;
} else {
    return 'auth fail';
    }
```

# Pusher 사용 방법 

- composer require pusher/pusher-php-server "~4.0"
- pusher 홈페이지 create app 
- bootstrap.js 
```javascript
import Echo from 'laravel-echo';

window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true
});
```

- env 
```php 
PUSHER_APP_ID=1145876
PUSHER_APP_KEY=7ed3a4ce8ebfe9741f98
PUSHER_APP_SECRET=a42171617fb6bbb4204c
PUSHER_APP_CLUSTER=ap3


BROADCAST_DRIVER=pusher

```
- config/broadcasting.php

```php
'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => "ap3",
                'useTLS' => false,  //https - true 
            ],
        ], 
```
- config\app.php 
```php
    #주석 풀어주기 
    App\Providers\BroadcastServiceProvider::class,
```

- Providers\BroadcastServiceProvider.php 
```php
    public function boot()
    {
        Broadcast::routes();

        require base_path('routes/channels.php');
    }

    
```
- php artisan config:clear 

- php artisan make:event MessageSent 
```php
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MyEvent implements ShouldBroadcast
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public $message;

  public function __construct($message)
  {
      $this->message = $message;
  }
//채널 이름
  public function broadcastOn()
  {
      return ['my-channel'];
  }
//이벤트 이름
  public function broadcastAs()
  {
      return 'my-event';
  }
}
```
- 컨트롤러 -> MessageSent::dispatch() 인자값으로 메시지 보내기 (use 반드시)

# AWS VS code 연동 
- VSCode의 extension항목에서 ftp-simple을 검색 후 install 클릭
- f1키 눌러주고 ftp-simple : Config - FTP connection setting을 찾아서 클릭
- json파일이 보이는데 이 json파일을 수정해 줘야함
- 위과 같이 수정
<br>name: name
<br>host: AWS public IP주소 또는 DNS
<br>port: ssh 이므로 sftp다. 따라서 22번 포트가 default( ftp는 21 )
<br>type: ssh 이므로 sftp
<br>username: aws 접속시 username
<br>password: 없으면 공란
<br>path: 접속 성공시 default경로
<br>privateKeyPath: aws pem키 로컬 경로
<br>
- F1키 눌러서 ftp-simple : Remote directory open to workspace 선택 -> “privateKeyPath”가 아니라 “privateKey” 다
- f1눌러서 enter누르다보면 /home/ubuntu
- 권한도 변경해줘야함


# AWS 연동 
- GRANT ALL PRIVILEGES ON *.* TO 'phpmyadmin'@'localhost' WITH GRANT OPTION;
- FLUSH PRIVILEGES;
- SELECT User, Host, plugin FROM mysql.user;   
- sudo vi config-db.php
- cd phpmyadmin/
- DB 주소보기 (env 파일)

[출처] (https://velog.io/@wimes/AWS-%EA%B0%9C%EB%B0%9C%ED%99%98%EA%B2%BD-%EC%84%A4%EC%A0%95-3-92k28oayef)




![Laravel best practices](/images/logo-english.png?raw=true)

번역:

[한국어](https://github.com/xotrs/laravel-best-practices) (by [임영록(cherrypick)](https://github.com/xotrs))

[Русский](https://github.com/alexeymezenin/laravel-best-practices/)(by [alexeymezenin](https://github.com/alexeymezenin))

[Português](https://github.com/jonaselan/laravel-best-practices) (by [jonaselan](https://github.com/jonaselan))

[Tiếng Việt](https://chungnguyen.xyz/posts/code-laravel-lam-sao-cho-chuan) (by [Chung Nguyễn](https://github.com/nguyentranchung))

이 문서가 도움이 되셨다면 현재 레퍼지토리뿐만 아니라, 원본 레퍼지토리도 한 번씩 star를 눌러주시면 감사하겠습니다. :D
<br>
원본 레퍼지토리: https://github.com/alexeymezenin/laravel-best-practices

이 문서는 라라벨 프레임워크에서 객체지향 디자인의 5원칙(SOLID), 패턴 등을 적용한 내용이 아닙니다. 라라벨 프레임워크로 프로젝트를 진행하면서 놓칠 수 있는 Best practice에 대해 정리한 글입니다.



## Contents
[단일 책임 원칙](#단일-책임-원칙)

[모델은 무겁게, 컨트롤러는 가볍게](#모델은-무겁게-컨트롤러는-가볍게)

[Validation-유효성 검사](#validation-유효성-검사)

[비즈니스 로직은 서비스 클래스에 있어야 합니다.](#비즈니스-로직은-서비스-클래스에-있어야-합니다)

[중복 배제(Don't repeat yourself)](#중복-배제dont-repeat-yourself)

[Query Builder, raw SQL 쿼리보다 Eloquent를 사용하는 것이 좋습니다.](#query-builder-raw-sql-쿼리보다-eloquent를-사용하는-것이-좋습니다)

[Mass assignment-대량 할당](#mass-assignment-대량-할당)

[블레이드 템플릿에서 쿼리를 실행하지 않습니다. 그리고 즉시 로딩을 사용합니다.(N + 1 문제)](#블레이드-템플릿에서-쿼리를-실행하지-않습니다-그리고-즉시-로딩을-사용합니다n--1-문제)

[코드에 주석을 작성합니다. 하지만 주석보다 의미있는 메서드 이름과 변수 이름을 사용하는 것이 더 좋습니다.](#코드에-주석을-작성합니다-하지만-주석보다-의미있는-메서드-이름과-변수-이름을-사용하는-것이-더-좋습니다)

[블레이드 템플릿에 JS와 CSS를 작성하지 않고 PHP 클래스에 HTML을 작성하지 않습니다.](#블레이드-템플릿에-js와-css를-작성하지-않고-php-클래스에-html을-작성하지-않습니다)

[코드에 텍스트로 작성하지 않고, 설정 파일, 언어 파일, 상수 등을 사용합니다.](#코드에-텍스트로-작성하지-않고-설정-파일-언어-파일-상수-등을-사용합니다)

[라라벨 커뮤니티에서 수용하는 표준 라라벨 도구를 사용합니다.](#라라벨-커뮤니티에서-수용하는-표준-라라벨-도구를-사용합니다)

[라라벨 네이밍 규칙을 따릅니다.](#라라벨-네이밍-규칙을-따릅니다)

[될 수 있으면 짧고 읽기 쉬운 문법을 사용합니다.](#될-수-있으면-짧고-읽기-쉬운-문법을-사용합니다)

[new Class 대신 IoC 컨테이너 또는 파사드를 사용합니다.](#new-class-대신-ioc-컨테이너-또는-파사드를-사용합니다)

[.env 파일에서 직접 데이터를 가져오지 않습니다.](#env-파일에서-직접-데이터를-가져오지-않습니다)

[날짜를 표준 형식으로 저장합니다. accessors(get), mutators(set)을 사용해 날짜 형식을 수정합니다.](#날짜를-표준-형식으로-저장합니다-accessorsget-mutatorsset을-사용해-날짜-형식을-수정합니다)

[또 다른 좋은 사례](#또-다른-좋은-사례)

### **단일 책임 원칙**

클래스와 메서드는 하나의 책임만 있어야 합니다.

나쁜 예:

```php
public function getFullNameAttribute()
{
    if (auth()->user() && auth()->user()->hasRole('client') && auth()->user()->isVerified()) {
        return 'Mr. ' . $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name;
    } else {
        return $this->first_name[0] . '. ' . $this->last_name;
    }
}
```

좋은 예:

```php
public function getFullNameAttribute()
{
    return $this->isVerifiedClient() ? $this->getFullNameLong() : $this->getFullNameShort();
}

public function isVerifiedClient()
{
    return auth()->user() && auth()->user()->hasRole('client') && auth()->user()->isVerified();
}

public function getFullNameLong()
{
    return 'Mr. ' . $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name;
}

public function getFullNameShort()
{
    return $this->first_name[0] . '. ' . $this->last_name;
}
```

[🔝 목차로 돌아가기](#contents)

### **모델은 무겁게, 컨트롤러는 가볍게**

DB와 관련된 로직은 Eloquent 모델이나 Repository 클래스에 작성되어야 합니다. 

나쁜 예:

```php
public function index()
{
    $clients = Client::verified()
        ->with(['orders' => function ($q) {
            $q->where('created_at', '>', Carbon::today()->subWeek());
        }])
        ->get();

    return view('index', ['clients' => $clients]);
}
```

좋은 예:

```php
public function index()
{
    return view('index', ['clients' => $this->client->getWithNewOrders()]);
}

class Client extends Model
{
    public function getWithNewOrders()
    {
        return $this->verified()
            ->with(['orders' => function ($q) {
                $q->where('created_at', '>', Carbon::today()->subWeek());
            }])
            ->get();
    }
}
```

[🔝 목차로 돌아가기](#contents)

### **Validation-유효성 검사**

유효성 검사 로직을 컨트롤러에서 Request 클래스로 옮깁니다.

나쁜 예:

```php
public function store(Request $request)
{
    $request->validate([
        'title' => 'required|unique:posts|max:255',
        'body' => 'required',
        'publish_at' => 'nullable|date',
    ]);

    ....
}
```

좋은 예:

```php
public function store(PostRequest $request)
{    
    ....
}

class PostRequest extends Request
{
    public function rules()
    {
        return [
            'title' => 'required|unique:posts|max:255',
            'body' => 'required',
            'publish_at' => 'nullable|date',
        ];
    }
}
```

[🔝 목차로 돌아가기](#contents)

### **비즈니스 로직은 서비스 클래스에 있어야 합니다.**

컨트롤러는 하나의 책임만 가지기 때문에 비즈니스 로직은 서비스 클래스에 있어야 합니다.


나쁜 예:

```php
public function store(Request $request)
{
    if ($request->hasFile('image')) {
        $request->file('image')->move(public_path('images') . 'temp');
    }
    
    ....
}
```

좋은 예:

```php
public function store(Request $request)
{
    $this->articleService->handleUploadedImage($request->file('image'));

    ....
}

class ArticleService
{
    public function handleUploadedImage($image)
    {
        if (!is_null($image)) {
            $image->move(public_path('images') . 'temp');
        }
    }
}
```

[🔝 목차로 돌아가기](#contents)

### **중복 배제(Don't repeat yourself)**

코드를 재사용합니다. 단일 책임 원칙뿐만 아니라 블레이드 템플릿, Eloquent 스코프 등은 코드의 중복을 피할 수 있도록 도와줍니다.

나쁜 예:

```php
public function getActive()
{
    return $this->where('verified', 1)->whereNotNull('deleted_at')->get();
}

public function getArticles()
{
    return $this->whereHas('user', function ($q) {
            $q->where('verified', 1)->whereNotNull('deleted_at');
        })->get();
}
```

좋은 예:

```php
public function scopeActive($q)
{
    return $q->where('verified', 1)->whereNotNull('deleted_at');
}

public function getActive()
{
    return $this->active()->get();
}

public function getArticles()
{
    return $this->whereHas('user', function ($q) {
            $q->active();
        })->get();
}
```

[🔝 목차로 돌아가기](#contents)

### **Query Builder, raw SQL 쿼리보다 Eloquent를 사용하는 것이 좋습니다.**

Eloquent를 사용하면 읽기 쉽고 유지 보수할 수 있는 코드를 작성할 수 있습니다. Eloquent는 소프트 삭제, 이벤트, 스코프 등 좋은 기능이 있습니다.

나쁜 예:


```sql
SELECT *
FROM `articles`
WHERE EXISTS (SELECT *
              FROM `users`
              WHERE `articles`.`user_id` = `users`.`id`
              AND EXISTS (SELECT *
                          FROM `profiles`
                          WHERE `profiles`.`user_id` = `users`.`id`) 
              AND `users`.`deleted_at` IS NULL)
AND `verified` = '1'
AND `active` = '1'
ORDER BY `created_at` DESC
```

좋은 예:

```php
Article::has('user.profile')->verified()->latest()->get();
```

[🔝 목차로 돌아가기](#contents)

### **Mass assignment-대량 할당**

나쁜 예:

```php
$article = new Article;
$article->title = $request->title;
$article->content = $request->content;
$article->verified = $request->verified;
// Add category to article
$article->category_id = $category->id;
$article->save();
```

좋은 예:

```php
$category->article()->create($request->all());
```

[🔝 목차로 돌아가기](#contents)

### **블레이드 템플릿에서 쿼리를 실행하지 않습니다. 그리고 즉시 로딩을 사용합니다.(N + 1 문제)**

나쁜예 (유저 전체를 가져오는 쿼리(1번) + 해당 유저의 프로필을 가져오는 쿼리(100번) = 101번 실행):

```php
@foreach (User::all() as $user)
    {{ $user->profile->name }}
@endforeach
```

좋은 예 (유저 전체를 가져오는 쿼리(1번) + 해당 유저의 프로필을 가져오는 쿼리(1번) = 2번 실행):

```php
$users = User::with('profile')->get();

...

@foreach ($users as $user)
    {{ $user->profile->name }}
@endforeach
```

[🔝 목차로 돌아가기](#contents)

### **코드에 주석을 작성합니다. 하지만 주석보다 의미있는 메서드 이름과 변수 이름을 사용하는 것이 더 좋습니다.**

나쁜 예:

```php
if (count((array) $builder->getQuery()->joins) > 0)
```

조금 더 나은 예:

```php
// Determine if there are any joins.
if (count((array) $builder->getQuery()->joins) > 0)
```

좋은 예:

```php
if ($this->hasJoins())
```

[🔝 목차로 돌아가기](#contents)

### **블레이드 템플릿에 JS와 CSS를 작성하지 않고 PHP 클래스에 HTML을 작성하지 않습니다.**

나쁜 예:

```php
let article = `{{ json_encode($article) }}`;
```

조금 더 나은 예:

```php
<input id="article" type="hidden" value="{{ json_encode($article) }}">

Or

<button class="js-fav-article" data-article="{{ json_encode($article) }}">{{ $article->name }}<button>
```

자바스크립트 파일:

```php
let article = $('#article').val();
```

The best way is to use specialized PHP to JS package to transfer the data.

[🔝 목차로 돌아가기](#contents)

### **코드에 텍스트로 작성하지 않고, 설정 파일, 언어 파일, 상수 등을 사용합니다.**

나쁜 예:

```php
public function isNormal()
{
    return $article->type === 'normal';
}

return back()->with('message', 'Your article has been added!');
```

좋은 예:

```php
public function isNormal()
{
    return $article->type === Article::TYPE_NORMAL;
}

return back()->with('message', __('app.article_added'));
```

[🔝 목차로 돌아가기](#contents)

### **라라벨 커뮤니티에서 수용하는 표준 라라벨 도구를 사용합니다.**

써드파티 패키지 및 도구 대신 내장되어있는 라라벨 기능과 커뮤니티 패키지를 사용합니다. 프로젝트에 참여하게 되는 개발자는 새로운 도구에 대해 학습을 해야합니다. 또한 써드파티 패키지나 도구를 사용할 때 라라벨 커뮤니티의 도움을 받을 수 있는 기회가 줄어듭니다. 

Task | Standard tools | 3rd party tools
------------ | ------------- | -------------
Authorization | Policies | Entrust, Sentinel and other packages
Compiling assets | Laravel Mix | Grunt, Gulp, 3rd party packages
Development Environment | Homestead | Docker
Deployment | Laravel Forge | Deployer and other solutions
Unit testing | PHPUnit, Mockery | Phpspec
Browser testing | Laravel Dusk | Codeception
DB | Eloquent | SQL, Doctrine
Templates | Blade | Twig
Working with data | Laravel collections | Arrays
Form validation | Request classes | 3rd party packages, validation in controller
Authentication | Built-in | 3rd party packages, your own solution
API authentication | Laravel Passport | 3rd party JWT and OAuth packages
Creating API | Built-in | Dingo API and similar packages
Working with DB structure | Migrations | Working with DB structure directly
Localization | Built-in | 3rd party packages
Realtime user interfaces | Laravel Echo, Pusher | 3rd party packages and working with WebSockets directly
Generating testing data | Seeder classes, Model Factories, Faker | Creating testing data manually
Task scheduling | Laravel Task Scheduler | Scripts and 3rd party packages
DB | MySQL, PostgreSQL, SQLite, SQL Server | MongoDB

[🔝 목차로 돌아가기](#contents)

### **라라벨 네이밍 규칙을 따릅니다.**

 [PSR 표준](http://www.php-fig.org/psr/psr-2/)을 따릅니다.
 
또한 라라벨 커뮤니티에서 수용하고 있는 네이밍 규칙을 따릅니다:

What | How | Good | Bad
------------ | ------------- | ------------- | -------------
Controller | singular | ArticleController | ~~ArticlesController~~
Route | plural | articles/1 | ~~article/1~~
Named route | snake_case with dot notation | users.show_active | ~~users.show-active, show-active-users~~
Model | singular | User | ~~Users~~
hasOne or belongsTo relationship | singular | articleComment | ~~articleComments, article_comment~~
All other relationships | plural | articleComments | ~~articleComment, article_comments~~
Table | plural | article_comments | ~~article_comment, articleComments~~
Pivot table | singular model names in alphabetical order | article_user | ~~user_article, articles_users~~
Table column | snake_case without model name | meta_title | ~~MetaTitle; article_meta_title~~
Model property | snake_case | $model->created_at | ~~$model->createdAt~~
Foreign key | singular model name with _id suffix | article_id | ~~ArticleId, id_article, articles_id~~
Primary key | - | id | ~~custom_id~~
Migration | - | 2017_01_01_000000_create_articles_table | ~~2017_01_01_000000_articles~~
Method | camelCase | getAll | ~~get_all~~
Method in resource controller | [table](https://laravel.com/docs/master/controllers#resource-controllers) | store | ~~saveArticle~~
Method in test class | camelCase | testGuestCannotSeeArticle | ~~test_guest_cannot_see_article~~
Variable | camelCase | $articlesWithAuthor | ~~$articles_with_author~~
Collection | descriptive, plural | $activeUsers = User::active()->get() | ~~$active, $data~~
Object | descriptive, singular | $activeUser = User::active()->first() | ~~$users, $obj~~
Config and language files index | snake_case | articles_enabled | ~~ArticlesEnabled; articles-enabled~~
View | snake_case | show_filtered.blade.php | ~~showFiltered.blade.php, show-filtered.blade.php~~
Config | snake_case | google_calendar.php | ~~googleCalendar.php, google-calendar.php~~
Contract (interface) | adjective or noun | Authenticatable | ~~AuthenticationInterface, IAuthentication~~
Trait | adjective | Notifiable | ~~NotificationTrait~~

[🔝 목차로 돌아가기](#contents)

### **될 수 있으면 짧고 읽기 쉬운 문법을 사용합니다.**

나쁜 예:

```php
$request->session()->get('cart');
$request->input('name');
```

좋은 예:

```php
session('cart');
$request->name;
```

더 많은 예시:

Common syntax | Shorter and more readable syntax
------------ | -------------
`Session::get('cart')` | `session('cart')`
`$request->session()->get('cart')` | `session('cart')`
`Session::put('cart', $data)` | `session(['cart' => $data])`
`$request->input('name'), Request::get('name')` | `$request->name, request('name')`
`return Redirect::back()` | `return back()`
`is_null($object->relation) ? $object->relation->id : null }` | `optional($object->relation)->id`
`return view('index')->with('title', $title)->with('client', $client)` | `return view('index', compact('title', 'client'))`
`$request->has('value') ? $request->value : 'default';` | `$request->get('value', 'default')`
`Carbon::now(), Carbon::today()` | `now(), today()`
`App::make('Class')` | `app('Class')`
`->where('column', '=', 1)` | `->where('column', 1)`
`->orderBy('created_at', 'desc')` | `->latest()`
`->orderBy('age', 'desc')` | `->latest('age')`
`->orderBy('created_at', 'asc')` | `->oldest()`
`->select('id', 'name')->get()` | `->get(['id', 'name'])`
`->first()->name` | `->value('name')`

[🔝 목차로 돌아가기](#contents)

### **new Class 대신 IoC 컨테이너 또는 파사드를 사용합니다.**

new Class 문법은 클래스 간의 결합도를 높이고 테스트를 복잡하게 만듭니다. new Class 문법 대신에 IoC 컨테이너 또는 파사드를 사용합니다.

나쁜 예:

```php
$user = new User;
$user->create($request->all());
```

좋은 예:

```php
public function __construct(User $user)
{
    $this->user = $user;
}

....

$this->user->create($request->all());
```

[🔝 목차로 돌아가기](#contents)

### **`.env` 파일에서 직접 데이터를 가져오지 않습니다.**

데이터를 설정 파일에 전달한 다음 `config()` helper 함수를 통해 애플리케이션에서 데이터를 사용합니다.

나쁜 예:

```php
$apiKey = env('API_KEY');
```

좋은 예:

```php
// config/api.php
'key' => env('API_KEY'),

// Use the data
$apiKey = config('api.key');
```

[🔝 목차로 돌아가기](#contents)

### **날짜를 표준 형식으로 저장합니다. accessors(get), mutators(set)을 사용해 날짜 형식을 수정합니다.**

나쁜 예:

```php
{{ Carbon::createFromFormat('Y-d-m H-i', $object->ordered_at)->toDateString() }}
{{ Carbon::createFromFormat('Y-d-m H-i', $object->ordered_at)->format('m-d') }}
```

좋은 예:

```php
// Model
protected $dates = ['ordered_at', 'created_at', 'updated_at']
public function getSomeDateAttribute($date)
{
    return $date->format('m-d');
}

// View
{{ $object->ordered_at->toDateString() }}
{{ $object->ordered_at->some_date }}
```

[🔝 목차로 돌아가기](#contents)

### **또 다른 좋은 사례**

라우트 파일에 로직을 작성하지 않습니다.

블레이드 템플릿에 바닐라 PHP의 사용을 최소화합니다.

[🔝 목차로 돌아가기](#contents)
