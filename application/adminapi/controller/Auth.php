<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;

class Auth extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $param = input();
        $where = [];
        if(!empty($param['keyword'])){
            $where ['auth_name'] =['like',"%{$param['keyword']}%"];
        }
        
        $list = \app\common\model\Auth::where($where)->select();
        $list = (new \think\Collection($list))->toArray();
        if(!empty($param['type']) && $param['type']=='tree'){
            $list = get_tree_list($list);
        }else{
            $list = get_cate_list($list);
        }
        $this->ok($list);

    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $param = input();
        $validate = $this->validate($param,[
            'auth_name'=>'require',
            'pid'=>'require',
            'is_nav'=>'require'
        ]);
        if($validate !== true){
            $this->fail($validate,401);
        }
        if($param['pid'] == 0){
            $param['level'] =0;
            $param['pid_path'] =0;
            $param['pid_path'] =0;
        }else{
            $info = \app\common\model\Auth::find($param['pid']);
            if(empty($info)){
                $this->fail('数据异常');
            }
            $param['pid_path'] = $info['pid_path'].'_'.$info['id'];
            $param['level'] = $info['level'] + 1;
        }

        $auth = \app\common\model\Auth::create($param,true);
        $list = \app\common\model\Auth::find($auth['id']);
        $this->ok($list);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {

        $list = \app\common\model\Auth::field('id,auth_name,pid,pid_path,auth_c,auth_a,is_nav,level')->find($id);
        $this->ok($list);
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $param = $request->param();
        $validate = $this->validate($param,[
            'auth_name'=>'require',
            'pid'=>'require',
            'is_nav'=>'require'
        ]);
        if($validate !== true){
            $this->fail($validate,401);
        }
        $msg = \app\common\model\Auth::find($id);
        if($param['pid'] == 0){
            $param['pid_path'] = 0;
            $param['level'] = 0;
        }else if($param['pid'] != $msg['pid']){
            $info = \app\common\model\Auth::find($param['pid']);
            if(empty($info)){
                $this->fail('数据异常',403);
            }
            $param['pid_path'] = $info['pid_path'].'_'.$info['id'];
            $param['level'] = $info['level'] + 1;
        }
        

        \app\common\model\Auth::update($param,['id'=>$id],true);

        $list = \app\common\model\Auth::find($id);
        $this->ok($list);

    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $total = \app\common\model\Auth::where('pid',$id)->count();
        if($total > 0){
            $this->fail('有子权限，不能删除');
        }
        \app\common\model\Auth::destroy($id);
        $this->ok();
    }

    public function nav(){
        $user_id = input('user_id');
        
        $navs = \app\common\model\Admin::find($user_id);
        $role_id = $navs['role_id'];
        if($role_id == 1){
            $data = \app\common\model\Auth::where('is_nav',1)->select();
        }else{
            $list = \app\common\model\Role::find($role_id);
            $role_ids = $list['role_auth)ids'];
            $data = \app\common\model\Auth::where('id','in',$role_ids)->select();
        }
        $data = (new \think\Collection($data))->toArray();
        $list = get_tree_list($data);
        $this->ok($list);
    }
}
