<?php

namespace app\common\model;

use think\Model;

class Goods extends Model
{
    use \traits\model\SoftDelete;
    public function types(){
        return $this->belongsTo('Type','type_id','id')->bind('type_name');
    }
    public function type(){
        return $this->belongsTo('Type','type_id','id');
    }
    public function cates(){
        return $this->belongsTo('Category','cate_id','id')->bind('cate_name');
    }
    public function category(){
        return $this->belongsTo('Category','cate_id','id');
    }
    public function brands(){
        return $this->belongsTo('Brand','brand_id','id')->bind(['brand_name'=>'name']);
    }
    public function brand(){
        return $this->belongsTo('Brand','brand_id','id');
    }
    public function specGoods(){
        return $this->hasMany('SpecGoods','goods_id','id');
    }
    public function goodsImages(){
        return $this->hasMany('GoodsImages','goods_id','id');
    }

    public static function getGoodsWithSpec($spec_goods_id,$goods_id){
        if($spec_goods_id){
            $where = ['t2.id' => $spec_goods_id];
        }else{
            $where = ['t1.id' => $goods_id];
        }
        $goods = self::alias('t1')
        ->join('pyg_spec_goods t2','t1.id=t2.goods_id','left')
        ->field('t1.*,t2.value_ids,t2.value_names,t2.price,t2.cost_price as cost_price2,t2.store_count')->where($where)->find();
        if($goods['price'] > 0){
            $goods['goods_price'] = $goods['price'] ;
        }
        if($goods['cost_price2'] > 0){
            $goods['cost_price'] = $goods['cost_price2'];
        }
        return $goods;
    }
}
