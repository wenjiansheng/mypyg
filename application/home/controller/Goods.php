<?php

namespace app\home\controller;

use think\Controller;
use think\Request;

class Goods extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index($id)
    {
        $goods = \app\common\model\Goods::where('cate_id',$id)->order('id desc')->paginate(10);
        $cate = \app\common\model\Category::find($id);
        
        return view('list',['goods'=>$goods,'cate_info'=>$cate]);
    }

    public function detail($id){
        $goods_info = \app\common\model\Goods::find($id);
        $thumb_img = \app\admin\model\GoodsImages::where('goods_id',$id)->select();
        $goods_info['goods_attr'] = json_decode($goods_info['goods_attr'],true);

        $spec_goods = \app\common\model\SpecGoods::where('goods_id',$goods_info['id'])->select();
        if(!empty($spec_goods)){
            if($spec_goods[0]['price'] > 0){
                $goods_info['goods_price'] = $spec_goods[0]['price'];
            }

            if($spec_goods[0]['cost_price'] > 0){
                $goods_info['cost_price'] = $spec_goods[0]['cost_price'];
            }

            if($spec_goods[0]['store_count'] > 0){
                $goods_info['store_count'] = $spec_goods[0]['store_count'];
            }

        }
        $value_ids = array_unique(explode('_',implode('_',array_column($spec_goods,'value_ids'))));
        $spec = \app\common\model\SpecValue::with('spec')->where('id','in',$value_ids)->select();
        $spec = (new \think\Collection($spec))->toArray();
        $spec_goods = (new \think\Collection($spec_goods))->toArray();
        $res = [];
        foreach($spec as $v){
            $res[$v['spec_id']] = [
                'spec_id'=>$v['spec_id'],
                'spec_name' => $v['spec']['spec_name'],
                'spec_values' => []
            ];
            
        }
        foreach($spec as $v){
            $res[$v['spec_id']]['spec_values'][] = $v;
        }
        $map = [];
        foreach($spec_goods as $v){
            $map[$v['value_ids']] = ['id'=>$v['id'],'price'=>$v['price']];
        } 
        // dump($map);
        $map = json_encode($map);      
        return view('item',['goods_info'=>$goods_info,'thumb_img'=>$thumb_img,'specs'=>$res,'spec_goods'=>$map]);
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
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        
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
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
