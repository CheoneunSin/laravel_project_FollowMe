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
            <div class="card">
                <div class="card-header">진료내역 결제</div>
                <div class="card-body">
                    <table class="table table-hover">
                        <tr>
                            <th scope="col">성함</th>
                            <td>{{ $patient->patient_name }}</td> 
                        </tr>
                        <tr>
                            <th scope="col">결제수단</th>
                            <td>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio1" value="option1">
                                    <label class="form-check-label" for="inlineRadio1">신용카드</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio2" value="option2">
                                    <label class="form-check-label" for="inlineRadio2">실시간계좌이체</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio2" value="option2">
                                    <label class="form-check-label" for="inlineRadio3">가상계좌</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio2" value="option2">
                                    <label class="form-check-label" for="inlineRadio4">휴대폰 결제</label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                        
                            <th scope="col">전화번호</th>
                            <td>
                               {{ $patient->phone_number }}
                            </td>
                        </tr>
                        <th scope="col" style="font-size: 18" >총 결제 금액</th>
                        <td class="font-weight-bold" style="color: coral; text-decoration: underline; font-style: oblique; text-align:end; font-size: 18" >{{ $storage }} 원</td>

                    </table>
                </div>
                <button onclick="iamport_event()" class="btn btn-primary btn-lg">결제하기</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="https://cdn.iamport.kr/js/iamport.payment-1.1.5.js"></script>
<script>
    function iamport_event() {
        var IMP = window.IMP; // 생략가능
        IMP.init('imp40035237'); // 'iamport' 대신 부여받은 "가맹점 식별코드"를 사용
        IMP.request_pay({
            pg: 'inicis', // version 1.1.0부터 지원.
            pay_method: 'card',
            merchant_uid: 'merchant_' + new Date().getTime(),
            name: '주문명:진료비결제',
            amount: @json($storage) ,
            buyer_name: @json($patient->patient_name),
            buyer_tel: @json($patient->phone_number),
            buyer_addr: @json($patient->address),
            buyer_postcode: @json($patient->postal_code),
            m_redirect_url: 'http://34.234.79.156/index.php/patient/iamport_end/' + @json($patient->patient_id);
        }, function(rsp) {
            if (rsp.success) {
                var msg = '결제가 완료되었습니다.';
                msg += '고유ID : ' + rsp.imp_uid;
                msg += '상점 거래ID : ' + rsp.merchant_uid;
                msg += '결제 금액 : ' + rsp.paid_amount;
                msg += '카드 승인번호 : ' + rsp.apply_num;
            } else {
                var msg = '결제에 실패하였습니다.';
                msg += '에러내용 : ' + rsp.error_msg;
            }
            alert(msg);
        });
    }
</script>
</body>
</html>
