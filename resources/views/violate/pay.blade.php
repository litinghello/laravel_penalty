{{--@extends('layouts.app')--}}
@extends('adminlte::page')

@section('content_header')
    <h1>扣分支付</h1>
@stop
@section('js')
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
@show
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('支付页面') }}</div>
                    <div class="card-body">
                        <div class="form-group">
                            @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    <ul style="color:red;">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <form method="POST" id="order_info" action="{{ route('wechats.violate.pay') }}">
                            @csrf
                            @if(session()->has('violate_info'))
                                @php
                                    $violate_info = session('violate_info');
                                    $info_object = [
                                    'violate_number'=>'决定书号',
                                    'violate_user_name'=>'车主姓名',
                                    'violate_car_number'=>'车牌号牌',
                                    //'violate_car_type'=>'车辆类型',
                                    'violate_money'=>'罚款额(元)',
                                    'violate_score'=>'扣分',
                                    'violate_illegal_place'=>'违法地点',
                                    'violate_illegal_time'=>'违法时间',
                                    //'violate_process_time'=>'处理时间',
                                    //'violate_behavior'=>'违法行为',
                                    //'violate_money_extra'=>'手续费',
                                    'violate_phone_number'=>'手机号码',
                                    ];
                                @endphp
                                @foreach ($info_object as $key => $value)
                                    <div class="form-group">
                                        <label for="{{$key}}" class="col-md-4 col-form-label text-md-right"></label>
                                        <div class="input-group">
                                            <div class="input-group-addon">{{ $value }}</div>
                                            <input name="{{$key}}" class="form-control" type="text" value="{{$violate_info[$key]?$violate_info[$key]:''}}" {{$violate_info[$key]?'readonly':''}} required>
                                        </div>
                                    </div>
                                @endforeach
                                @if ($errors->has('violate_phone_number'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('violate_phone_number') }}</strong>
                                    </span>
                                @endif
                                <div class="form-group row mb-0">
                                    <div class="text-center">
                                        <div id="wechat_pay" type="button" class="btn btn-primary">
                                            {{ __('微信支付') }}
                                        </div>
                                        <a class="btn btn-link" data-toggle="modal" data-target="#violate_info">
                                            {{ __('收费规则?') }}
                                        </a>
                                    </div>
                                </div>

                                <div class="modal fade" id="violate_info" tabindex="-1" role="dialog" aria-labelledby="violate_info_label" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title" id="violate_info_label">服务费</h4>
                                            </div>
                                            <div class="modal-body">罚款金额加扣分服务费110元/分，请注意：我们收取的是服务费。</div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                                            </div>
                                        </div><!-- /.modal-content -->
                                    </div><!-- /.modal -->
                                </div>
                            @else
                                {{--{{ __('数据错误') }}--}}
                            @endif
                        </form>
                        {{--<div id="text"></div>--}}
                        <script type="text/javascript">
                            var wechat_pay_data = null;
                            var wechat_pay_type = "JSSDK";//WeixinJSBridge or JSSDK
                            function wechat_pay(data){//微信两种支付方式，
                                if(wechat_pay_type === "WeixinJSBridge"){
                                    WeixinJSBridge.invoke(
                                        'getBrandWCPayRequest',data,
                                        function(res){
                                            if(res.err_msg === "get_brand_wcpay_request:ok" ) {
                                                window.location.replace("{{ route('views.home') }}");
                                            }else{
                                            }
                                        }
                                    );
                                }else if(wechat_pay_type === "JSSDK"){
                                    // document.getElementById("text").innerText= JSON.stringify(data);
                                    wx.config({
                                        debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
                                        appId:data['appId'] , // 必填，公众号的唯一标识
                                        timestamp: data['timestamp'] , // 必填，生成签名的时间戳
                                        nonceStr: data['nonceStr'], // 必填，生成签名的随机串
                                        //signature: data['paySign'],// 必填，签名，见附录1
                                        jsApiList: ['chooseWXPay'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
                                    });
                                    wx.ready(function(){
                                        wx.chooseWXPay({
                                            debug: false,timestamp:data['timestamp'] ,nonceStr: data['nonceStr'] ,
                                            package: data['package'] ,signType: data['signType'] ,paySign: data['paySign'] , // 支付签名
                                            success: function (res) {
                                                window.location.replace("{{ route('views.home') }}");// 支付成功后的回调函数
                                            },
                                            cancel: function(res) {
                                                alert('支付取消');//支付取消
                                            },
                                            fail: function(res) {
                                                //接口调用失败时执行的回调函数。
                                                // alert("fail"+JSON.stringify(res));//支付取消
                                            }
                                        });
                                    });
                                }
                            }
                            $("#wechat_pay").click(function(){
                                //这里防止重复加载支付订单
                                if(wechat_pay_data !== null){
                                    wechat_pay(wechat_pay_data);
                                }else{
                                    var order_info = $("#order_info").serialize();
                                    $.ajax({
                                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                                        url:"{{route('wechats.violate.pay')}}",type:"POST",data:order_info,
                                        success:function(data){
                                            if(data['status'] === 0){
                                                wechat_pay_data = data['data'];//保存值
                                                // document.getElementById("text").innerText = JSON.stringify(wechat_pay_data);
                                                wechat_pay(wechat_pay_data);//采用微信网页支付
                                            }else{
                                                alert(data['data']);
                                            }
                                        },
                                        error:function(error){
                                            alert("请再次提交");
                                        }
                                    });
                                }
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection




