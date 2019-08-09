<?php

namespace app\adminapi\controller;

use think\Controller;

class BaseApi extends Controller
{
    protected $no_login = ['login/captcha', 'login/login'];
    
    public function _initialize()
    {
        parent::_initialize();
        //允许的源域名
        header("Access-Control-Allow-Origin: *");
        //允许的请求头信息
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept,   Authorization");
        //允许的请求类型
        header('Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH');

      /*   try{
            $path = strtolower($this->request->controller()) . '/' . $this->request->action();
            if(!in_array($path, $this->no_login)){
                $user_id = \tools\jwt\Token::getUserId();
                if(empty($user_id)){
                    $this->fail('token验证失败', 403);
                }

                $this->request->get(['user_id'=> $user_id]);
                $this->request->post(['user_id'=> $user_id]);

                if(!\app\adminapi\logic\AuthLogic::check()){
                    $this->fail('没有权限访问');
                };
            }
        }catch(\Exception $ex){
            // $error = $ex->getMessage();
            // $this->fail($error);
            $this->fail('服务异常，请检查token令牌', 403);
        }; */ 
        $this->request->get(['user_id'=> 1]);
        $this->request->post(['user_id'=> 1]);
    }   
    
    protected function response($code=200, $msg='success', $data=[])
    {
        $res = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ];
        //以下两行二选一
        //echo json_encode($res, JSON_UNESCAPED_UNICODE);die;
        json($res)->send();die;
    }

    protected function ok($data=[],$code=200,$msg='success'){
        return $this->response($code,$msg,$data);
    }

    protected function fail($msg='fail',$code=400){
        return $this->response($code,$msg);
    }


}
