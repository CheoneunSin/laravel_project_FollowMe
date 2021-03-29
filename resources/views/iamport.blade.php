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
<div id="app" class="container" style="font-family: 'Nanum Gothic', sans-serif; ">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <br><br>
            <div class="card">
                <div class="card-header">診療内訳決済</div>
                <div class="card-body">
                    <table class="table table-hover">
                        <tr>
                            <th scope="col" width="30%">お名前</th>
                            <td>{{ $patient->patient_name }}</td> 
                        </tr>
                        <tr>
                            <th scope="col">決済手段</th>
                            <td>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="pay_method" id="inlineRadio1" value="card">
                                    <label class="form-check-label" for="inlineRadio1">クレジットカード</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="pay_method" id="inlineRadio2" value="trans">
                                    <label class="form-check-label" for="inlineRadio2">リアルタイム口座振替</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="pay_method" id="inlineRadio2" value="vbank">
                                    <label class="form-check-label" for="inlineRadio3">仮想口座</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="pay_method" id="inlineRadio2" value="phone">
                                    <label class="form-check-label" for="inlineRadio4">携帯決済</label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                        
                            <th scope="col">電話番号</th>
                            <td>
                               {{ $patient->phone_number }}
                            </td>
                        </tr>
                        <th scope="col" style="font-size: 18" >総決済金額</th>
                        <td class="font-weight-bold" style="color: coral; text-decoration: underline; font-style: oblique; text-align:end; font-size: 18" >{{ $storage }} 円</td>

                    </table>
                </div>
                <button onclick="iamport_event()" class="btn btn-primary btn-lg">決済する</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="https://cdn.iamport.kr/js/iamport.payment-1.1.5.js"></script>
<script>
    function iamport_event() {
        try {
            var pay_method_value = document.querySelector('input[name="pay_method"]:checked').value
            var IMP = window.IMP; // 생략가능
            IMP.init('imp40035237'); // 'iamport' 대신 부여받은 "가맹점 식별코드"를 사용
            IMP.request_pay({
                pg: 'inicis', // version 1.1.0부터 지원.
                pay_method: pay_method_value,
                merchant_uid: 'merchant_' + new Date().getTime(),
                name: '診療費決済',
                amount: @json($storage) ,
                // amount: 10 ,
                buyer_name: @json($patient->patient_name),
                buyer_tel: @json($patient->phone_number),
                buyer_addr: @json($patient->address),
                buyer_postcode: @json($patient->postal_code),
                m_redirect_url: 'http://34.234.79.156/index.php/patient/iamport_end/' + @json($patient_id)
            }, function(rsp) {
                if (rsp.success) {
                    var msg = '決済が完了しました。';
                    msg += '固有ID : ' + rsp.imp_uid;
                    msg += '商店取引ID : ' + rsp.merchant_uid;
                    msg += '決済金額 : ' + rsp.paid_amount;
                    msg += 'カード承認番号 : ' + rsp.apply_num;
                } else {
                    var msg = '決済に失敗しました。';
                    msg += 'エラー内容 : ' + rsp.error_msg;
                }
                alert(msg);
            });
        } catch (error) {
            alert("お支払い方法を選択してください。");
        }    
    }
</script>
</body>
</html>
