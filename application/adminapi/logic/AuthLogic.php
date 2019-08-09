<?php
namespace app\adminapi\logic;

use think\console\command\make\Controller;

class AuthLogic {
    public static function check(){
        $controller = request()->controller();
        $action = request()->action();
        if($controller == 'Index' && $action == 'index'){
            return true;
        }
        $role = \app\common\model\Role::find(input('username'));
        $role_id = $role['role_id'];
        if($role_id == 1){
            return true;
        }
        $auth = \app\common\model\Auth::find($role_id);
        $auths = $auth['role_auth_ids'];
        $info = \app\common\model\Auth::where('auth_c',$controller)->where('auth_a',$action)->select();
        $ids = $info['id'];
        if(in_array($ids,explode(',',$auths))){
            return true;
        }
        return false;
    }
}