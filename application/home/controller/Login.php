<?php

namespace app\home\controller;

use think\Controller;

class Login extends Controller
{
    public function login(){
        $this->view->engine->layout(false);
        return view('login');
    }

    public function dologin(){
        $param = input();
        $validate = $this->validate($param,[
            'username|用户名'=>'require',
            'password|密码'=>'require|length:5,18'
        ]);
        if($validate !== true){
            $this->error($validate);
        }

        $password = encrypt_password($param['password']);
        $res = \app\common\model\User::where(function($query)use($param){$query->where('phone',$param['username'])->whereOr('email',$param['username']);})->where('password',$password)->find();

        if($res){
            session('user_info',$res->toArray());
            \app\home\logic\CartLogic::cookieToDb();
            // if(session('back_url')){
            //     $this->redirect(session('back_url'));
            // }
            $back_url = session('back_url') ?: 'home/index/index';            
            $this->redirect($back_url);
        }else{
            $this->error('用户名或者密码错误');
        }
    }

    public function logout(){
        session(null);
        $this->redirect('home/login/login');
    }

    public function register(){
       
        $this->view->engine->layout(false);
        return view();
    }

    public function phone(){
        $param = input();
        $validate = $this->validate($param,[
            'phone|手机号码'=>'require|unique:user,phone|regex:1[3-9]\d{9}',
            'password|密码'=>'require|length:5,16|confirm:repassword',
            'code|验证码'=>'require|length:6'
        ]);
        if($validate !== true){
            $this->error($validate);
        }
        $register_id = cache('register_id'.$param['phone']);
        if($param['code'] != $register_id){
            $this->error('验证码不正确');
        }
        cache('register_id'.$param['phone'],null);
        $param['password'] = encrypt_password($param['password']);
        $param['username'] = $param['phone'];
        $param['nickname'] = encrypt_phone($param['phone']);
        \app\common\model\User::create($param,true);
        $this->redirect('home/login/login');
    }

    public function sendcode(){
        $param = input();        
        $validate = $this->validate($param,[
            'phone|手机号码'=>'require|regex:1[3-9]\d{9}'
        ]);
        if($validate !== true){
            $msg = ['code'=>400,'msg'=>$validate];
            return json($msg);
        }
        $register_time = cache('register_time'.$param['phone']);
        if(time()-$register_time < 60){
            $msg=['code'=>400,'msg'=>'发送频繁'];
            return json($msg);
        }
        $code = mt_rand(100000,999999);
        $content = "【创信】你的验证码是：{$code}，3分钟内有效！";
        $result = sendmsg($param['phone'],$content);
        // $result = true;
        if($result === true){
            cache('register_id'.$param['phone'],$code,180);
            cache('register_time'.$param['phone'],time(),180);
            $msg = ['code'=>200,'msg'=>'发送成功','data'=>$code];
            return json($msg);
        }else{
            $msg = ['code'=>400,'msg'=>$result];
            return json($msg);
        }
    }

    public function qqcallback(){
        require_once('./plugins/qq/API/qqConnectAPI.php');
        $qc = new \QC;
        $access_token = $qc->qq_callback();
        $openid = $qc->get_openid();
        $qc = new \QC($access_token,$openid);
        $info = $qc->get_user_info();

        $user = \app\common\model\User::where('open_type','qq')->where('openid',$openid)->find();
        if($user){
            $user['nickname'] = $info['nickname'];
            $user->save();
        }else{
            \app\common\model\User::create(['open_type'=>'qq','openid'=>$openid]);
        }
        $user = \app\common\model\User::where('open_type','qq')->where('openid',$openid)->find();
        session('user_info',$user->toArray());
        \app\home\logic\CartLogic::cookieToDb();
        $this->redirect('home/index/index');

    }
}
