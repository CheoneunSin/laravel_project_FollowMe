<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nanum+Gothic&family=Nanum+Pen+Script&display=swap');
</style>
<div class="container" style="font-family: 'Nanum Gothic', sans-serif; ">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <br><br>
            <div class="card">
                <div class="card-header">진료 결제 완료</div>
                <div class="card-body">
                    <table class="table table-hover">
                        <tr>
                            <th scope="col">성함</th>
                            <td>{{ $patient->patient_name }}</td> 
                        </tr>
                        <th scope="col" style="font-size: 18" >총 결제 금액</th>
                        <td class="font-weight-bold" style="color: coral; text-decoration: underline; font-style: oblique; text-align:end; font-size: 18" >{{ $storage }} 원</td>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
