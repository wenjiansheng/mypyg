<?php

namespace app\common\model;

use think\Model;

class Category extends Model
{
    use \traits\model\SoftDelete;
    public function brands(){
        return $this->hasMany('Brand','cate_id','id');
    }

    
}
