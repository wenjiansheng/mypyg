<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;
use app\common\model\Category as CategoryModel;

class Category extends BaseApi
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
        if(isset($param['pid'])){
            $where['pid'] = $param['pid'];
        }
        $list =  CategoryModel::where($where)->select();
        $list = (new \think\Collection($list))->toArray();
        if(!isset($param['type']) || $param['type'] != 'list'){
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
            'cate_name|分类名'=>'require',
            'pid|父级id'=>'require|integer|gt:0',
            'is_show|是否显示 '=>'require|in:0,1',
            'is_hot|是否热门'=>'require|in:0,1',
            'sort|排序'=>'require|between:0,9999',
        ]);
        if($validate !== true){
            $this->fail($validate);
        }
        if(isset($param['image_url']) && !empty($param['image_url']) && is_file('.'.$param['image_url'])){
            \think\Image::open('.'.$param['image_url'])->thumb(80,120)->save('.'.$param['image_url']);
        }
        if($param['pid'] == 0){
            $param['level'] =0;
            $param['pid_path'] = 0;
            $param['pid_path_name'] = '';
        }else{
            $res = CategoryModel::find($param['pid']);
            $param['level'] = $res['level'] + 1;
            $param['pid_path'] = $res['pid_path'].'-'.$res['id'];
            $param['pid_path_name'] = $res['pid_path_name'].'-'.$res['cate_name'];

        }
        $param['image_url'] = isset($param['image_url'])??'';
        $res = CategoryModel::create($param,true);
        $info = CategoryModel::find($res['id']);
        $this->ok($info);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $info = CategoryModel::find($id);
        $this->ok($info);
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
            'cate_name|分类名'=>'require',
            'pid|父级id'=>'require|integer|gt:0',
            'is_show|是否显示 '=>'require|in:0,1',
            'is_hot|是否热门'=>'require|in:0,1',
            'sort|排序'=>'require|between:0,9999',
        ]);
        if($validate !== true){
            $this->fail($validate);
        }
        if($param['pid'] == 0){
            $param['level'] =0;
            $param['pid_path'] = 0;
            $param['pid_path_name'] = '';
        }else{
            $res = CategoryModel::find($param['pid']);
            $param['level'] = $res['level'] + 1;
            $param['pid_path'] = $res['pid_path'].'-'.$res['id'];
            $param['pid_path_name'] = $res['pid_path_name'].'-'.$res['cate_name'];

        }
        if(isset($param['logo']) && !empty($param['logo']))
            $param['image_url'] = $param['logo'];
        CategoryModel::update($param,['id'=>$id],true);
        $info = CategoryModel::find($id);
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
        
        $num = CategoryModel::where('pid',$id)->count();
        if($num > 0){
            $this->fail('有子权限，不能删');
        }
        CategoryModel::destroy($id);
        $this->ok();
    }
}
