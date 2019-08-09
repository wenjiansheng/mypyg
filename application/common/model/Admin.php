<?php

namespace app\common\model;

use think\Model;

class Admin extends Model
{
    use \traits\model\SoftDelete;

    public function profile(){
        return $this->hasOne('Profile','uid');
    }
}