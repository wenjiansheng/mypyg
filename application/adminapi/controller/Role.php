<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;
use app\common\model\Role as RoleModel;

class Role extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $list = RoleModel::select();
        foreach($list as &$v){
            $role_ids = $v['role_auth_ids'];
            $auths = \app\common\model\Auth::where('id','in',$role_ids)->select();
            $v['role_auths'] = [];
            $auths = (new \think\Collection($auths))->toArray();
            $auths = get_tree_list($auths);
            $v['role_auths']=$auths;
        }
        unset($v);
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
        $param = $request->param();
        $validate = $this->validate($param,[
            'role_name|角色名称'=>'require',
            'auth_ids|权限id'=>'require',
        ]);
        if($validate !== true){
            $this->fail($validate);
        }
        $param['role_auth_ids'] = $param['auth_ids'];
        $res = RoleModel::create($param,true);
        $list = RoleModel::find($res['id']);
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
        $list = RoleModel::field('id,role_name,desc,role_auth_ids')->find($id);
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
        $param = input();
        $validate = $this->validate($param,[
            'role_name|角色名称'=>'require',
            'auth_ids|权限'=>'require'
        ]);
        if($validate !== true){
            $this->fail($validate);
        }
        $param['role_auth_ids'] = $param['auth_ids'];
        RoleModel::update($param,['id'=>$id],true);
        $list = RoleModel::find($id);
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
        if($id == 1){
            $this->fail('这个角色不能删除');
        }
        $num = \app\common\model\Admin::where('role_id',$id)->count();
        if($num > 0){
            $this->fail('角色使用中 不能删除');

        }
        RoleModel::destroy($id);
        $this->ok();
    }
}
