<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AuthorizationsController extends Controller
{
    public function weapplogin(Request $request)
    {
        $code = $request->code;
	    // 根据 code 获取微信 openid 和 session_key
        $miniProgram = \EasyWeChat::miniProgram();
        $data = $miniProgram->auth->session($code);
        if (isset($data['errcode'])) {
            return $this->response->errorUnauthorized('code已过期或不正确');
        }
        $weappOpenid = $data['openid'];
        $weixinSessionKey = $data['session_key'];
        $nickname = $request->nickname;
	    $avatar = str_replace('/132', '/0', $request->avatar);
	    $country = $request->country;
        $province = $request->province;
        $city = $request->city;
        $gender = $request->gender=='1' ? 'male' :'female';
	    $language = $request->language;

        if($request->gender){
	        $gender = $request->gender=='1' ? 'male' :'female';
            $attributes['gender'] = $gender;
        }
        //找到 openid 对应的用户
        $user = User::where('weapp_openid', $weappOpenid)->first();
        //把session_key
        $attributes['weixin_session_key'] = $weixinSessionKey;

        if($nickname){
            $attributes['nickname'] = $nickname;
        }
        if (!$user) {
            User::create([
	            'username' => $weappOpenid,
                'weapp_openid' => $weappOpenid,
                'weapp_session_key' => $weixinSessionKey,
	            'password' => $weixinSessionKey.uniqid(),
                'is_active' => 0,
	            'avatar' => $this->avatarUpyun($avatar),
                'nickname' => $nickname,
	            'country' => $country,
                'province' => $province,
                'city' => $city,
                'gender' => $gender,
	            'language'=>$language,
            ]);
            // 获取对应的用户
            $user = User::where('weapp_openid', $weappOpenid)->first();
            $attributes['weapp_session_key'] = $weixinSessionKey;
        }
	    $attributes['updated_at'] = now();
	    // 更新用户数据
        $user->update($attributes);
        // 直接创建token并设置有效期
        $createToken = $user->createToken($user->weapp_openid);
        $createToken->token->expires_at = Carbon::now()->addDays(15);
        $createToken->token->save();
        $token = $createToken->accessToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => "Bearer",
            'expires_in' => Carbon::now()->addDays(15),
            'data'=>$user,
        ], 200);
    }
    private function avatarUpyun($avatar){
	    $avatarfile = file_get_contents($avatar);
	    $filename = 'avatars/'.'SD'.uniqid().'.png';
	    Storage::disk('upyun')->write($filename, $avatarfile);
	    $wexinavatar=config('filesystems.disks.upyun.protocol').'://'.config('filesystems.disks.upyun.domain').'/'.$filename;
	    return $wexinavatar;
    }

    public function weappShopRegister(Request $request){
        $userid = Auth::guard('api')->user()->id;
        $avatar_url = Auth::guard('api')->user()->avatar_url;
        $avatar = str_replace('/132', '/0', $avatar_url);// 替换为大图头像
        if($userid){
            $title = $request->title;
//            $phone = $request->phone;
            $summary = $request->summary;
            $country = $request->country;
            $province = $request->province;
            $cityInfo = $request->cityInfo;
            $address = $request->address;
            $villageInfo = $request->villageInfo;
            $longitude = $request->longitude;
            $latitude = $request->latitude;

//            $is_phone = User::where('phone', $phone)->first();
//            if ($is_phone) {
//                return response()->json([
//                    'status' => 'false',
//                    'message' => '手机号已存在',
//                ], 403);
//            }
            $is_shop = Shop::where('title', $title)->first();
            $is_user = Shop::where('user_id', $userid)->first();
            if ($is_shop) {
                return response()->json([
                    'status' => 'false',
                    'message' => '名称已存在',
                ], 403);
            }
            if ($is_user) {
                return response()->json([
                    'status' => 'false',
                    'message' => '您已注册过',
                ], 403);
            }
            if (!$title) {
                return response()->json([
                    'status' => 'false',
                    'message' => '名称不能为空',
                ], 403);
            }


            $avatarfile = file_get_contents($avatar);
//            $file_extension = strtolower(substr(strrchr('http://veg.name/images-pc/mg-code-mp.jpg',"."),1));
            $filename = 'avatars/'.$userid.'MG'.uniqid().'.png';
            Storage::disk('upyun')->write($filename, $avatarfile);
            $shopavatar=config('filesystems.disks.upyun.protocol').'://'.config('filesystems.disks.upyun.domain').'/'.$filename;

            $shop=Shop::create([
                'user_id' => $userid,
                'title' => $title,
                'summary' => $summary,
                'avatar' => $shopavatar,
                'country' => $country,
                'province' => $province,
                'cityInfo' => $cityInfo,
                'address' => $address,
                'villageInfo' => $villageInfo,
                'longitude' => $longitude,
                'latitude' => $latitude,
                'code' => 'https://images.veg.kim/mp/mg-code-mp.jpg',
            ]);
//            //生成小程序码
            $shopId=$shop->id;
//            $access = json_decode($this->get_access_token(),true);
//            $access_token= $access['access_token'];
//            $url = config('wxxcx.getwxacodeunlimit_url') . $access_token;
//            $data='{"width":"430","auto_color":true,"path":"pages/detail/detail?id='.$shopId.'"}';
//            $da = $this->api_notice_increment($url,$data);
//            if ($da) {
//                $filename = 'qrcode/'.$shopId.'MG'.uniqid().'.png';
//                Storage::disk('upyun')->write($filename, $da);
//                $shopCode=config('filesystems.disks.upyun.protocol').'://'.config('filesystems.disks.upyun.domain').'/'.$filename;
//
//                $attributes['code'] = $shopCode;
//                $shop->update($attributes);
//            }
            //更新用户状态为已入驻，is_active=1
            $user = User::find($userid);
//            $attributes['phone'] = $phone;
            $attributes['is_active'] = '1';
            // 更新用户数据
            $user->update($attributes);
            return response()->json([
                'status' => 'true',
                'message' => '成功入驻',
                'user' => $user,
                'shop'=> $shop,
                'shopId'=> $shopId
            ], 200);
        }
        return $this->response->errorUnauthorized('请重新申请入驻');
    }

    public function get_access_token(){
        $url =  config('wxxcx.qrcode_url');
        return $data = $this->curl_get($url);
    }

    //获得二维码
    public function getQrcode() {
//        header('content-type:image/gif');
        header('content-type:image/png');//格式自选，不同格式貌似加载速度略有不同，想加载更快可选择jpg
        //header('content-type:image/jpg');
        $shopId = Auth::guard('api')->user()->shop->id;
        $shop=Shop::find($shopId);
        if(!$shop->code){
            $access = json_decode($this->get_access_token(),true);
            $access_token= $access['access_token'];
            $url = config('wxxcx.getwxacodeunlimit_url') . $access_token;
            $data='{"width":"430","auto_color":true,"path":"pages/detail/detail?id='.$shopId.'"}';
            $da = $this->api_notice_increment($url,$data);
            if (!$da) {
                return response()->json([
                    'status' => 'false',
                    'message' => '二维码生成失败',
                ], 403);
            }
            $filename = 'qrcode/'.$shopId.'MG'.uniqid().'.png';
            Storage::disk('upyun')->write($filename, $da);
            $shopCode=config('filesystems.disks.upyun.protocol').'://'.config('filesystems.disks.upyun.domain').'/'.$filename;

            $attributes['code'] = $shopCode;
            $shop->update($attributes);

            return response()->json([
                'status' => 'true',
                'message' => '小程序码生成成功',
                'code'=>$shopCode
            ], 200);
        }
        return response()->json([
            'status' => 'false',
            'message' => '二维码已存在',
        ], 403);
    }
    function api_notice_increment($url, $data){
        $ch = curl_init();
        $header = "Accept-Charset: utf-8";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        //     var_dump($tmpInfo);
        //    exit;
        if (curl_errno($ch)) {
            return false;
        }else{
            // var_dump($tmpInfo);
            return $tmpInfo;
        }
    }
    public function curl_get($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        return $data;
    }


    public function weappRegister(Request $request)
    {
        $code = $request->code;

        // 根据 code 获取微信 openid 和 session_key
        $miniProgram = \EasyWeChat::miniProgram();
        $data = $miniProgram->auth->session($code);

        // 如果结果错误，说明 code 已过期或不正确，返回 401 错误
        if (isset($data['errcode'])) {
            return $this->response->errorUnauthorized('code已过期或不正确');
//            return response()->json([
//                'status' => 'false',
//                'message' => 'code已过期或不正确',
//            ], 401);
        }

        //找到 openid 对应的用户
        $user = User::where('weapp_openid', $data['openid'])->first();
        //把session_key
        $attributes['weixin_session_key'] = $data['session_key'];

        // 未找到对应用户则新建用户
        if (!$user) {
            // 如果未提交用户名密码，403 错误提示
            $title = $request->title;
            $phone = $request->phone;
            $avatar = $request->avatar;
            $nickname = $request->nickname;

            $is_phone = User::where('phone', $phone)->first();
            if ($is_phone) {
                return response()->json([
                    'status' => 'false',
                    'message' => '手机号已存在',
                ], 403);
            }
            $is_shop = Shop::where('title', $title)->first();
            if ($is_shop) {
                return response()->json([
                    'status' => 'false',
                    'message' => '店铺名不能重复',
                ], 403);
            }
            User::create([
                'weapp_openid' => $data['openid'],
                'weixin_session_key' => $data['session_key'],
                'phone' => $phone,
                'avatar_url' => $avatar,
                'nickname' => $nickname,
            ]);
            $userid = User::where('phone', $phone)->first()->id;
            $summary = $request->summary;
            $address = $request->address;
            $villageInfo = $request->villageInfo;
            $longitude = $request->longitude;
            $latitude = $request->latitude;
            Shop::create([
                'user_id' => $userid,
                'title' => $title,
                'summary' => $summary,
                'avatar' => $avatar,
                'address' => $address,
                'villageInfo' => $villageInfo,
                'longitude' => $longitude,
                'latitude' => $latitude,
            ]);

            // 获取对应的用户
            $user = User::where('weapp_openid', $data['openid'])->first();
            $attributes['weapp_openid'] = $data['openid'];
        }

        // 更新用户数据
        $user->update($attributes);

        // 为对应用户创建 JWT
        // $token = Auth::guard('api')->fromUser($user);

        // 直接创建token并设置有效期
        $createToken = $user->createToken($user->weapp_openid);
        $createToken->token->expires_at = Carbon::now()->addDays(15);
        $createToken->token->save();
        $token = $createToken->accessToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => "Bearer",
            'expires_in' => '21600',
            'data'=>$user
        ], 200);

//        return response()->respondWithToken($token)->setStatusCode(201);
    }

    public function update()
    {
        $user = Auth::guard('api')->user();
        $createToken = $user->createToken($user->weapp_openid);

        $createToken->token->expires_at = Carbon::now()->addDays(15);
        $createToken->token->save();

        $token = $createToken->accessToken;
        return response()->json([
            'access_token' => $token,
            'token_type' => "Bearer",
            'expires_in' => '21600',
            'data'=>$user
        ]);
    }

    public function destroy()
    {
        if (Auth::guard('api')->check()) {
            Auth::guard('api')->user()->token()->revoke();

        }
        return response()->json([
            'status' => 'true',
            'message' => '退出成功',
        ], 204);
    }
}
