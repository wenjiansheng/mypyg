<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;
use app\common\model\Brand as BrandModel;

class Brand extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
       
        $where = [];
        
        if(!empty(input('cate_id'))){
            $cate_id = input('cate_id');
            $where['cate_id'] = $cate_id; 
            $list = BrandModel::field('id,name')->where($where)->select();
        }else{
            if(!empty(input('keyword'))){
                $keyword = input('keyword');
                $where['name'] = ['like',"%$keyword%"];
            }
            $list = BrandModel::alias('t1')->where($where)->join('pyg_category t2','t1.cate_id=t2.id','left')->field('t1.id,name,url, t1.logo,desc,t1.sort,t1.is_hot,cate_name')->paginate(10);
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
            'name|品牌名称'=>'require',
            'cate_id|所属分类id'=>'require|integer|gt:0',
            'is_hot|是否热门'=>'require|in:0,1',
            'sort|排序'=>'require|integer|gt:0',

        ]);
        if($validate !== true){
            $this->fail($validate);
        }
        if(isset($param['logo']) && !empty($param['logo']) && is_file('.'.$param['logo'])){
            \think\Image::open('.'.$param['logo'])->thumb(80,120)->save('.'.$param['logo']);
        }
        $info = BrandModel::create($param,true);
        $data = BrandModel::field('id,name,url, logo,desc,sort,is_hot,cate_id')->find($info['id']);
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
        $list = BrandModel::field('id,name,url, logo,desc,sort,is_hot,cate_id')->find($id);
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
            'name|品牌名称'=>'require',
            'cate_id|所属分类id'=>'require|integer|gt:0',
            'is_hot|是否热门'=>'require|in:0,1',
            'sort|排序'=>'require|integer|gt:0',

        ]);
        if($validate !== true){
            $this->fail($validate);
        }
        if(isset($param['logo']) && !empty($param['logo']) && is_file('.'.$param['logo'])){
            \think\Image::open('.'.$param['logo'])->thumb(80,120)->save('.'.$param['logo']);
        }else{
            unset($param['logo']);
        }
        BrandModel::update($param,['id'=>$id],true);
        $data = BrandModel::field('id,name,url, logo,desc,sort,is_hot,cate_id')->find($id);
        $this->ok($data);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $sum = \app\common\model\Goods::where('brand_id',$id)->find();
        if($sum){
            $this->fail('不能删');
        }
        BrandModel::destroy($id);
        $this->ok();
    }
}
