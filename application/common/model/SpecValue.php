<?php

namespace app\common\model;

use think\Model;

class SpecValue extends Model
{
    use \traits\model\SoftDelete;
    public function spec(){
        return $this->belongsTo('Spec','spec_id');
    }
}
