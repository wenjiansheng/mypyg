<?php

namespace app\common\model;

use think\Model;

class Profile extends Model
{
    public function admin(){
        return $this->hasOne('Admin','id','uid')->bind('username,email');
    }
}
