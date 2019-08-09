<?php

namespace app\common\model;

use think\Model;

class Cart extends Model
{
    function goods(){
        return $this->belongsTo('Goods','goods_id','id')->bind('goods_name,goods_logo,goods_price,goods_number,frozen_number,goods_desc,cost_price');
    }

    function specGoods(){
        return $this->belongsTo('SpecGoods','spec_goods_id','id')->bind(['value_names','price','store_count','store_frozen','cost_price2'=>'cost_price','value_ids']);
    }
}
