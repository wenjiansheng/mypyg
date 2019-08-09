<?php
namespace app\adminapi\controller;

class Index extends BaseApi
{
    public function index()
    {
       $list = \app\common\model\Profile::with('admin')->select();
       $this->ok($list);
    }
}
