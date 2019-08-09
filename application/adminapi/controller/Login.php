<?php

namespace app\adminapi\controller;

use think\Controller;

class Login extends BaseApi
{
   
    public function captcha(){
        $uniqid = uniqid(mt_rand(100000, 999999));

        $data = [
            'src' => captcha_src($uniqid),
            'uniqid' => $uniqid
        ];
        $this->ok($data);
    }

    public function login(){
        $param = input();

        $validate = $this->validate($param, [
            'username' => 'require',
            'password' => 'require',
            // 'code' => 'require',
            'uniqid' => 'require'
        ]);
        if($validate !== true){
            $this->fail($validate);
        }
        
        $session_id = cache('session_id_'.$param['uniqid']);
        if($session_id){
            session_id($session_id);
        }
        
        // if (!captcha_check($param['code'], $param['uniqid'])) {
            
            // $this->fail('验证码错误');
        // }
        
        $where = [
            'username' => $param['username'],
            'password' => encrypt_password($param['password'])
        ];
        $info = \app\common\model\Admin::where($where)->find();
        if(!$info){
            
            $this->fail('用户名或者密码错误');
        }
        $data['token'] = \tools\jwt\Token::getToken($info->id);
        $data['user_id'] = $info->id;
        $data['username'] = $info->username;
        $data['nickname'] = $info->nickname;
        $data['email'] = $info->email;
        
        $this->ok($data);

    }

    public function logout(){
        $token = \tools\jwt\Token::getRequestToken();
        $delete_token = cache('delete_token') ?: [];
        $delete_token[]=$token;
        cache('delete_token',$delete_token,86400);
        $this->ok();
    }
}
