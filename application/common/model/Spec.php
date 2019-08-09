<?php

namespace app\common\model;

use think\Model;

class Spec extends Model
{
    use \traits\model\SoftDelete;
    public function specValues(){
        return $this->hasMany('SpecValue','spec_id');
    }
}
