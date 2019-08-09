<?php

namespace app\home\controller;

use think\Controller;
use think\Request;

class Base extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $category = \app\common\model\Category::select();
        $info = (new \think\Collection($category))->toArray();
        $list = get_tree_list($info);
        $this->assign('list',$list);
    }
}
