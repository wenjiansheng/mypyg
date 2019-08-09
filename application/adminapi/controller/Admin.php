<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;
use app\common\model\Admin as AdminModel;

class Admin extends BaseApi
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
       if(!empty($param['keyword'])) {
           $keyword = $param['keyword'];
            $where['user_name'] = ['like',"%$keyword%"];
       }
       $data = AdminModel::alias('t1')
       ->join('pyg_role t2','t1.role_id=t2.id','left')
       ->field('t1.id,username,email,nickname,last_login_time,status,role_name')
       ->where($where)->paginate(3);
    //    $role_name = \app\common\model\Role::
       $this->ok($data);
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
        'username|用户名'=>'require|length:3,12|unique:admin',
        'email|邮箱'=>'require|email',
        'role_id|角色id'=>'require|integer|gt:0',
        'password|初始密码'=>'length:6,18'
        ]);
        if($validate !== true){
            $this->fail($validate);
        }
        if(empty($param['password'])){
            $param['password'] = '123456';
        }
        $param['password'] = encrypt_password($param['password']);
        $param['nickname'] = $param['username'];
        $res = AdminModel::create($param,true);
        $data = AdminModel::field('id,username,email,nickname,last_login_time,status,role_id')->find($res['id']);
        $this->ok($data);
        
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $list = AdminModel::field('id,username,email,nickname,last_login_time,status,role_id')->find($id);
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
        if($id == 1){
            $this->fail('超级管理员不能变更密码');
        }

        if(!empty($param['type']) && $param['type'] == 'reset_pwd'){
            $param['password'] = encrypt_password('123456');
        }else{
            $validate = $this->validate($param,[
                'nickname|昵称'=>'',
                'email|邮箱'=>'email',
                'role_id|角色ID'=>'integer',
            ]);
            if($validate !== true){
                $this->fail($validate);
            }
        }
        unset($param['username']);
        unset($param['password']);
        AdminModel::update($param,['id'=>$id],true);
        $info = AdminModel::find($id);
        $this->ok($info);
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
            $this->fail('能不能删心里没数？');
        }
        if($id == input('user_id')){
            $this->fail('别把自己删了啊');

        }
        AdminModel::destroy($id);
        $this->ok();
    }
}
