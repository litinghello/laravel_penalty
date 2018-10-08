<?php

namespace App\Http\Controllers;

use App\User;
use App\PenaltyInfo;
use App\WechatOrder;
use App\ThirdAccount;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\json_decode;
use Yajra\Datatables\Datatables;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use EasyWeChat\Payment\Order;

class PenaltiesController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    //用于只允许通过认证的用户访问指定的路由
    public function __construct()
    {
        $this->middleware('auth');
    }
    //添加第三方账户
    //http://localhost/laravel/penalties/account/add?account_type=51jfk&account_name=123456&account_password=123456
    public function add_third_account(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_type' => 'required|alpha_num',
            'account_name' => 'required|alpha_num',
            'account_password' => 'required|alpha_num'
        ]);
        if ($validator->fails()) {
            return $validator->errors()->first();
        }
        switch ($request['account_type']) {
            case "51jfk":
                return redirect()->route('penalties.login.51jfk', ['account_name' => $request['account_name'], 'account_password' => $request['account_password'], 'account_type' => $request['account_type']]);
                break;
            default:
                return "账号类型设置错误";
                break;
        }
    }

    //登录第三方账户
    public function login_51jfk_account(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_type' => 'required|alpha_num|in:51jfk',
            'account_name' => 'required|alpha_num',
            'account_password' => 'required|alpha_num',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->first();
        }
        //1.获取图片
        $client = new Client();//
        //$client = new Client(['cookies' => true]);// 可开启共享
        $jar = new CookieJar();
        $server_addr = "http://www.51jfk.com/index.php/Index/verify/";//获取验证图片
        $response = $client->get($server_addr, [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest',
                'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                'Cookie' => ''
            ],
            'cookies' => $jar //读取cookie
        ]);
        $cookies_str = "";
        foreach ($jar->getIterator() as $item) {
            $cookies_str = $cookies_str . $item->getName() . "=" . $item->getValue() . "; ";
        }
        //echo $cookies_str;//打印cookie
        $image_data = (string)Image::make($response->getBody())->encode('data-url');
        $image_data_url = explode(',', $image_data)[1];
        //echo $data;
        //echo "<img src=\"{$image_data}\" />";
        //2.解码识别
        $server_addr = "http://47.105.52.97/";//识别
        $response = $client->post($server_addr, [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest',
                'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                'Cookie' => ''
            ],
            'body' => $image_data_url
        ]);
        $verify_code = $response->getBody();
        //echo $verify_code;
        //3.验证图片 用于测试验证是否成功
        $server_addr = 'http://www.51jfk.com/index.php/Index/check_verify.html';
        $response = $client->post($server_addr, [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest',
                'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                'Cookie' => $cookies_str
            ],
            'body' => 'verify=' . $response->getBody()
        ]);
        //echo $response->getBody();
        $response_body = json_decode($response->getBody(), true);
        if ($response_body['code'] == 0) {
            return redirect()->route('penalties.login.51jfk', ['account_name' => $request['account_name'], 'account_password' => $request['account_password'], 'account_type' => $request['account_type']]);
        }
        //4.验证登录
        $server_addr = "http://www.51jfk.com/index.php/Index/tclogin.html";
        $response = $client->post($server_addr, [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest',
                'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                'Cookie' => $cookies_str
            ],
            'body' => "username={$request['name']}&userpwd={$request['password']}&verify=" . $verify_code,
            'cookies' => $jar //读取cookie
        ]);
        $cookies_str = "";
        foreach ($jar->getIterator() as $item) {
            $cookies_str = $cookies_str . $item->getName() . "=" . $item->getValue() . "; ";
        }

        $response_body = json_decode($response->getBody(), true);
        if ($response_body != null) {
            if ($response_body['status'] == 0) {
                //return redirect()->route('penalties.login.51jfk',['name'=>$request['account_name'],'password'=>$request['account_password']]);//echo "验证失败";
                return ['status' => 1, 'cookie' => ''];
            }
        } else {
            $account = ThirdAccount::where("account_name", $request['account_name'])->first();
            if (!$account) {
                $account = new ThirdAccount;//未找到用户进行创建
            }
            $account->account_type = $request['account_type'];
            $account->account_name = $request['account_name'];
            $account->account_password = $request['account_password'];
            $account->account_status = "valid";
            $account->account_cookie = $cookies_str;
            $account->account_reserve = "";
            $account->save();
        }
        //echo $cookies_str;//打印cookie
        return ['status' => 0, 'cookie' => $cookies_str];
    }


    /**决定书编号查询违法信息
     * @param Request $request
     * @return string
     */
    //5101041204594064
    public function penalty_info(Request $request){
//        return back()->withErrors(['penalty_number'=>'此激活用定过！']);
        $validator = Validator::make($request->all(), [
            'penalty_number' => 'required|alpha_num|between:15,16',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 1,'data' => $validator->errors()->first()]);
        }
        $penalty_number = $request['penalty_number'];

        // 这里需要实现 已经存在直接返回（10分钟内）
        $penaltyinfo = PenaltyInfo::where('penalty_number', $penalty_number)->first();
        if ($penaltyinfo != null) {
            if ($penaltyinfo->updated_at > date("Y-m-d H:i:s", strtotime("-100000 minute"))) {
                return response()->json(['status' => 0,'data' => [$penaltyinfo]]);
            }
        }
        $account = ThirdAccount::where("account_status", 'valid')->where("account_type", '51jfk')->first();
        if (!$account) {
            $account = ThirdAccount::where("account_type", '51jfk')->first();
            if ($account) {
                return redirect()->route('penalties.login.51jfk', ['name' => $account['account_name'], 'password' => $account['account_password']]);//echo "验证失败";
            } else {
                return response()->json(['status' => 1,'data' =>  '请添加账户！']);
            }
        }

        $url = 'http://www.51jfk.com/index.php/Fakuan/fkdjg';
//        $cookies = 'PHPSESSID=rf6jd40r10mq2djftcfgsrtpl7; temp_user=think%3A%7B%22username%22%3A%22temp_user222.211.251.209%22%2C%22day%22%3A%222018-07-11%22%2C%22day_query_count%22%3A%2220%22%7D; UM_distinctid=1648810ae8d31a-0c671bc72b0985-f373567-13c680-1648810ae8e5ac; CNZZDATA1000345804=1917868247-1531286351-null%7C1531286351; user=think%3A%7B%22memberid%22%3A%2250959%22%2C%22nickname%22%3A%22%22%2C%22membername%22%3A%2215228949671%22%2C%22weixin%22%3A%22%22%7D; Hm_lvt_f06eee151ce72cc27662fae694f526b8=1531291152,1531291535; Hm_lpvt_f06eee151ce72cc27662fae694f526b8=1531291535';
        //       $cookies = 'yunsuo_session_verify=4164ba9853b8fa004df7f1487ee107fc; temp_user=think%3A%7B%22username%22%3A%22temp_user222.211.235.249%22%2C%22day%22%3A%222018-09-05%22%2C%22day_query_count%22%3A%2220%22%7D; CNZZDATA1000345804=1169833295-1536113091-%7C1536113091; user=think%3A%7B%22memberid%22%3A%2258276%22%2C%22nickname%22%3A%22%22%2C%22membername%22%3A%2215228867020%22%2C%22weixin%22%3A%22%22%7D; PHPSESSID=ehtung44o0jb7c6ch7mnll77t5; UM_distinctid=165a7bf1b2633d-00856ede20d123-784a5037-1fa400-165a7bf1b2710c5; Hm_lvt_f06eee151ce72cc27662fae694f526b8=1536117644; Hm_lpvt_f06eee151ce72cc27662fae694f526b8=1536117644';
        $cookies = $account['account_cookie'];
        $body = "fkdbh=" . $penalty_number . "&type=outoinput";
        $client = new Client();
        $response = $client->post($url, [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest',
                'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                'Cookie' => $cookies
            ],
            'body' => $body
        ]);
//        $response->getStatusCode();
//        $response->getHeader('content-type');
//        $response->body();
        $response_code = $response->getStatusCode();
        if ($response_code != 200) {
            return response()->json(['status' => 1,'data' =>  '系统异常！']);
        }
        $response_body = json_decode($response->getBody(), true);
        if ($response_body['code'] != 200) {
            return response()->json(['status' => 1,'data' =>  "请求数据失败"]);
        }
        $penaltyinfo = PenaltyInfo::create([
            'penalty_number'=>$response_body['jdsbh'],
            'penalty_car_number'=>$response_body['hphm'],
            'penalty_car_type'=>$response_body['hpzl'],
            'penalty_money'=>$response_body['fkje'],
            'penalty_money_late'=>$response_body['znj'],
            'penalty_user_name'=>$response_body['dsr'],
            'penalty_process_time'=>date('Y-m-d H:i:s', strtotime($response_body['clsj'])),
            'penalty_illegal_time'=>date('Y-m-d H:i:s', strtotime($response_body['wfsj'])),
            'penalty_illegal_place'=>$response_body['wfdz'],
            'penalty_behavior'=>$response_body['wfxw'] . "",
        ]);
        return response()->json(['status' => 0,'data' =>  [$penaltyinfo]]);
    }
    /**车辆违法
     * @param Request $request
     * @return string
     */
    public function penalty_car_info(Request $request){
        $validator = Validator::make($request->all(), [
            'violate_car_number_province' => 'required',//省份  川
            'violate_car_number' => 'required|alpha_num',//号牌  A5F795
//            'violate_car_number_type' => 'required|alpha_num',//车辆种类  02 暂时支持小车
            'violate_car_frame_number' => 'required|alpha_num',//车架号后6位  010304
//            'violate_car_engine_number' => 'required|alpha_num',//车架号后6位  010304
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 1,'data' => $validator->errors()->first()]);
        }

        $lsprefix = $request['violate_car_number_province'];
        $lsnum = $request['violate_car_number'];
//        $lstype = $request['violate_car_number_type'];
        $frameno = $request['violate_car_frame_number'];


        $account = ThirdAccount::where("account_status", 'valid')->where("account_type", '51jfk')->first();
        if (!$account) {
            $account = ThirdAccount::where("account_type", '51jfk')->first();
            if ($account) {
                return redirect()->route('penalties.login.51jfk', ['name' => $account['account_name'], 'password' => $account['account_password']]);//echo "验证失败";
            } else {
                return response()->json(['status' => 1,'data' => '请添加账户！']);
            }
        }
        $url = 'http://www.51jfk.com/index.php/Weizhang/index.html';
        $body = "lsprefix=".$lsprefix."&lsnum=".$lsnum."&lstype=02&frameno=".$frameno."&engineno=&mobileno=&category=geren&cartype=feiyingyun&verify=3240&memberid=116&carorg=&api=CHETAIJI&addr=&isdirect=&is_dangerousgoods=1&checkcode=&postcphm=&tempuser=";
        $cookies = $account['account_cookie'];
        $client = new Client();
        $response = $client->post($url, [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest',
                'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                'Cookie' => $cookies
            ],
            'body' => $body
        ]);
        $response_code = $response->getStatusCode();
        if ($response_code != 200) {
            return response()->json(['status' => 1,'data' => "系统异常"]);
        }
        $response_body = json_decode($response->getBody(), true);
//        return $response_body;

        preg_match_all("/<ul.*?>.*?<\/ul>/ism", $response_body, $matches);
        $key = array('xh','info','dm','time','address','address','feiyong','koufen');
        $infos = array();
        foreach ($matches[0] as  $match){
            if(strpos($match,"<li class=\"fakuan\">") !== false){
                preg_match_all('/<li.*?>(.*?)<\/li>/is', $match, $sss);
                $info = array();
                for ($x=0; $x<=7; $x++) {
                    $info[$key[$x]] = $sss[1][$x];
                }
                if('违章信息' != $info['info']){
                    $infos[]=$info;
                }
            }else{
            }
        }
        return response()->json(['status' => 0,'data' => $infos]);

    }
 }
