<?php
namespace app\home\logic;

class CartLogic{
    public static function addcart($goods_id,$spec_goods_id,$number,$is_selected=1){
        if(session('?user_info')){
            $user_id = session('user_info.id');
            $where = [
                'user_id'=>$user_id,
                'goods_id'=>$goods_id,
                'spec_goods_id'=>$spec_goods_id
            ];
            $info = \app\common\model\Cart::where($where)->find();
            if($info){
                $info['number'] += $number;
                $info['is_selected'] = $is_selected;
                $info->save();
            }else{
                \app\common\model\Cart::create(['user_id'=>$user_id,'goods_id'=>$goods_id,'spec_goods_id'=>$spec_goods_id,'number'=>$number,'is_selected'=>$is_selected]);
            }
        }else{
            $data = cookie('cart')?:[];
            $key = $goods_id.'_'.$spec_goods_id;
            
            if(isset($data[$key])){
                $data[$key]['number']+=$number;
                $data[$key]['is_selected']+=$is_selected;
            }else{
                $res = [
                    'id'=>$key,
                    'user_id'=>'',
                    'goods_id'=>$goods_id,
                    'number'=>$number,
                    'spec_goods_id'=>$spec_goods_id,
                    'is_selected'=>$is_selected
                ];
                $data[$key]= $res;
            }
            cookie('cart',$data,86400*7);
            

        }
    }

    public static function getAllCart(){
        if(session('?user_info')){
            $user_id = session('user_info.id');
            $data = \app\common\model\Cart::where('user_id',$user_id)->field('id,user_id,goods_id,number,spec_goods_id,is_selected')->select();
            $data = (new \think\Collection($data))->toArray();
        }else{
            $data = cookie('cart')?:[];
            $data = array_values($data);
        }
        return $data;
    }

    public static function cookieToDb(){
        $cart = cookie('cart')?:[];

        foreach($cart as $v){
            self::addcart($v['goods_id'],$v['spec_goods_id'],$v['number']);
        }
        cookie('cart',null);
    }

    public static function changeNum($id,$number){
        if(session('?user_info')){
            $user_id = session('user_info.id');
            \app\common\model\Cart::update(['number'=>$number],['id'=>$id,'user_id'=>$user_id]);
        }else{
            $cart = session('cart')?:[];
            $cart[$id]['number'] =$number;
            session('cart',$cart,86400*7);
        }
    }

    public static function delCart($id){
        if(session('?user_info')){
            $user_id = session('user_info.id');
            \app\common\model\Cart::where(['id'=>$id,'user_id'=>$user_id])->delete();
        }else{
            $data = cookie('cart') ?: [];
            unset($data[$id]);
            cookie('cart',$data,86400*7);
        }
    }

    public static function changeStatus($id,$status){
        if(session('?user_info')){
            $user_id = session('user_info.id');
            $where['user_id'] = $user_id;
            if($id != 'all'){
                $where['id'] = $id;
            }
            \app\common\model\Cart::where($where)->update(['is_selected'=>$status]);
            
        }else{
            $data = cookie('cart') ?: [];
            if($id == 'all'){
                foreach($data as &$v){
                    $v['is_selected'] = $status;
                }
            }else{
                $data[$id]['is_selected'] = $status;
            }
            
            cookie('cart',$data,86400*7);
        }
    }
}