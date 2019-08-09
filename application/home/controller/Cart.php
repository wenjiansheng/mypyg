<?php

namespace app\home\controller;

use think\Controller;

class Cart extends Base
{
    public function addcart(){
        if(request()->isGet()){
            $this->redirect('home/index/index');
        }

        $param = input();
        $validate = $this->validate($param,[
            'goods_id'=>'require|integer|gt:0',
            'spec_goods_id'=>'integer|gt:0',
            'number'=>'require|integer|gt:0'
        ]);
        if($validate !== true){
            $this->error($validate);
        }

        \app\home\logic\CartLogic::addcart($param['goods_id'],$param['spec_goods_id'],$param['number']);
        $goods = \app\common\model\Goods::getGoodsWithSpec($param['spec_goods_id'],$param['goods_id']);
        return view('success-cart',['goods'=>$goods,'num'=>$param['number']]);
    }

    public function index(){
        $list = \app\home\logic\CartLogic::getAllCart();
        foreach($list as &$v){
            $goods = \app\common\model\Goods::getGoodsWithSpec($v['spec_goods_id'],$v['goods_id']);
            $v['goods'] = $goods;
        }
        unset($v);
        // $list = (new \think\Collection($list))->toArray();
        // dump($list);die;
        return view('cart',['goods'=>$list]);
    }

    public function changenum(){
        $param = input();
        $validate = $this->validate($param,[
            'id'=>'require',
            'number'=>'require|integer|gt:0'
        ]);
        if($validate !== true){
            $data = ['code'=>400,'msg'=>$validate,'data'=>''];
            return json($data);
        }

        \app\home\logic\CartLogic::changeNum($param['id'],$param['number']);
        $data = [
            'code' => 200,
            'msg' => '修改成功',
            'data' => '',
        ];
        echo json_encode($data);die;
    }

    public function delcart(){
        $param = input();
        if(!isset($param['id']) || empty($param['id'])){
            $data = [
                'code' => 400,
                'msg' =>'参数错误',
                'data' => ''
            ];
            return json($data);
        }

        \app\home\logic\CartLogic::delCart($param['id']);
        $data = [
            'code' => 200,
            'msg' =>'删除成功',
            'data' => ''
        ];
        return json($data);
    }

    public function changestatus(){
        $param = input();
        $validate = $this->validate($param,[
            'id'=>'require|integer|gt:0',
            'status'=>'require|in:0,1'
        ]);
        if($validate !== true){
            $res = [
                'code' => 400,
                'msg' => $validate,
            ];
            return json($res);
        }

        \app\home\logic\CartLogic::changeStatus($param['id'],$param['status']);
        $res = [
            'code' => 200,
            'msg' => '修改成功',
        ];
        return json($res);
    }
}
