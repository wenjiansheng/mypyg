<?php

namespace app\common\model;

use think\Model;
class Type extends Model
{
    use \traits\model\SoftDelete;

    protected $deleteTime = 'delete_time';
    public function specs(){
        return $this->hasMany('Spec','type_id');
    }

    public function attrs(){
        return $this->hasMany('Attribute','type_id');
    }
}
