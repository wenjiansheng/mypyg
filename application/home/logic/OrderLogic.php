<?php
namespace app\home\logic;

class OrderLogic{
    public static function getCartDataWithGoods(){
        $user_id = session('user_info.id');
        $cart_data = \app\common\model\Cart::with('goods,spec_goods')->where('is_selected',1)->where('user_id',$user_id)->select();
        $cart_data = (new \think\Collection($cart_data))->toArray();
        $total_number = 0;
        $total_price = 0;
        foreach($cart_data as &$v){
            if(isset($v['price']) && $v['price'] > 0){
                $v['goods_price'] = $v['price'];
            }
            if(isset($v['cost_price2']) && $v['cost_price2'] > 0){
                $v['cost_price'] = $v['cost_price2'];
            }
            if(isset($v['store_count']) && $v['store_count'] > 0){
                $v['goods_number'] = $v['store_count'];
            }
            if(isset($v['store_frozen']) && $v['store_frozen'] > 0){
                $v['frozen_number'] = $v['store_frozen'];
            }
            $total_number += $v['number'];
            $total_price += $v['number'] * $v['goods_price'];
        }
        unset($v);
        return [
            'cart_data'=>$cart_data,
            'total_number' => $total_number,
            'total_price' => $total_price
        ];
    }
}