<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
if(!function_exists('encrypt_password')){
    function encrypt_password($password){
        $salt = 'pinyougou';
        return md5(md5(trim($password)).$salt);
    }
}

if (!function_exists('get_cate_list')) {
    //递归函数 实现无限级分类列表
    function get_cate_list($list,$pid=0,$level=0) {
        static $tree = array();
        foreach($list as $row) {
            if($row['pid']==$pid) {
                $row['level'] = $level;
                $tree[] = $row;
                get_cate_list($list, $row['id'], $level + 1);
            }
        }
        return $tree;
    }
}

if(!function_exists('get_tree_list')){
    function get_tree_list($list){
        $temp = [];
        foreach($list as $k=>$v){
            $v['son'] =[];
            $temp[$v['id']] = $v;
        }
        foreach($temp as $k=>$v){
            $temp[$v['pid']]['son'][] = &$temp[$v['id']];
        }
        return isset($temp[0]['son']) ? $temp[0]['son'] : [];
    }

    if(!function_exists('curl_request')){
        function curl_request($url,$post=true,$param=[],$https=true){
            $ch = curl_init($url);
            if($post){
                curl_setopt($ch,CURLOPT_POST,true);
                curl_setopt($ch,CURLOPT_POSTFIELDS,$param);
            }
            if($https){
                curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
            }
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            $res = curl_exec($ch);
            curl_close($ch);
            return $res;

        }
    }

    if(!function_exists('sendmsg')){
        function sendmsg($phone,$content){
            $gateway = config('msg.gateway');
            $appkey = config('msg.appkey');

            $url = $gateway.'?appkey='.$appkey;
            $url .= '&mobile=' . $phone . '&content=' . $content;
            $param = ['mobile'=>$phone,'content'=>$content];
            $res = curl_request($url,false,[],true);
            if(!$res){
                return '请求发送失败';
            }
            $res = json_decode($res,true);
            if(isset($res['code']) && $res['code'] == 10000){
                return true;
            }else{
                return '短信发送失败';
            }
        }
    }

    if(function_exists('encrypt_phone')){
        function encrypt_phone($phone){
            return substr($phone,0,3).'****'.substr($phone,7);
        }
    }
}