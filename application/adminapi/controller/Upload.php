<?php

namespace app\adminapi\controller;

use think\Controller;

class Upload extends BaseApi
{
    public function logo(){
        $param =input();
        if(empty($param['type'])){
            $this->fail('缺少参数');
        }
        $file = request()->file('logo');
        if(empty($file)){
            $this->fail('没有上传图片');
        }

        $info = $file->validate(['size'=>10*1024*1024,'ext'=>'jpg,gif,png'])->move(ROOT_PATH.'public'.DS.'uploads'.DS.$param['type']);
        if($info){
            $logo = DS.'uploads'.DS.$param['type'].DS.$info->getSaveName();
            $this->ok($logo);
        }else{
            $err = $file->getError();
            $this->fail($err);
        }
    }

    public function images(){
        $param = input();
        $data = [];
        if(!isset($param['type'])){
            $param['type'] = 'goods';
        }
        $files = request()->file('images');
        if(!isset($files) || empty($files) ){
            $this->fail('必须上传文件');
        }
        
        foreach($files as $file){
            $dir = ROOT_PATH.'public'.DS.'uploads'.DS.$param['type'];
            if(!is_dir($dir)){
                mkdir($dir);
            }
            $info = $file->validate(['size'=>'1081024*1024','ext'=>'jpg,png,gif'])->move($dir);
            if($info){
                $data['success'][] = DS.'uploads'.DS.$param['type'].DS.$info->getSaveName();
            }else{
                $data['error'][] = ['name'=>$file->getInfo('name'),'msg'=>$file->getError()];
            }
        }
        $this->ok($data);
    }
}
