<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;
use app\common\model\Type as TypeModel;

class Type extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $list = TypeModel::field('id,type_name')->select();
        $this->ok($list);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $param = $request->param();
        $validate = $this->validate($param, [
            'type_name|模型名' => 'require',
            'spec|规格数组' => 'require|array',
            'attr|属性数组' => 'require|array'
        ]);
        if ($validate !== true) {
            $this->fail($validate);
        }

        \think\Db::startTrans();
        try {
            $type = \app\common\model\Type::create($param, true);
            foreach ($param['spec'] as $k => $spec) {
                if (trim($spec['name']) == '') {
                    unset($param['spec'][$k]);
                    continue;
                }
                foreach ($spec['value'] as $i => $value) {
                    if (trim($value) == '') {
                        unset($param['spec'][$k]['value'][$i]);
                    }
                }
                if (empty($param['spec'][$k]['value'])) {
                    unset($param['spec'][$k]);
                }
            }

            $spec_data = [];
            foreach ($param['spec'] as $k => $spec) {
                $spec_data[] = [
                    'type_id' => $type['id'],
                    'spec_name' => $spec['name'],
                    'sort' => $spec['sort']
                ];
            }
            $spec_obj = new \app\common\model\Spec();
            $spec_res = $spec_obj->saveAll($spec_data);
            $spec_value = [];
            foreach ($param['spec'] as $k => $spec) {

                foreach ($spec['value'] as $i => $value) {
                    $spec_value[] = [
                        'spec_id' => $spec_res[$k]['id'],
                        'spec_value' => $value,
                        'type_id' => $type['id']
                    ];
                }
            }
            (new \app\common\model\SpecValue())->saveAll($spec_value);

            foreach ($param['attr'] as $k => $attr) {
                if (trim($attr['name']) == '') {
                    unset($param['attr']['$k']);
                    continue;
                }
                foreach ($attr['value'] as $i => $value) {
                    if (trim($value) == '') {
                        unset($param['attr'][$k]['value'][$i]);
                    }
                }
            }
            $attr_data = [];
            foreach ($param['attr'] as $k => $attr) {

                $attr_data[] = [
                    'attr_name' => $attr['name'],
                    'type_id' => $type['id'],
                    'attr_values' => implode(',', $attr['value']),
                    'sort' => $attr['sort']
                ];
            }
            (new \app\common\model\Attribute())->saveAll($attr_data);

            \think\Db::commit();
            $info = \app\common\model\Type::field('id,type_name')->find($type['id']);
            $this->ok($info);
        } catch (\Exception $ex) {
            \think\Db::rollBack();
            $msg = $ex->getMessage();
            $this->fail($msg);
        }
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $list = TypeModel::with('specs,specs.spec_values,attrs')->find($id);
        $this->ok($list);
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $param = input();
        $validate = $this->validate($param, [
            'type_name|模型名' => 'require',
            'spec|规格数组' => 'require|array',
            'attr|属性数组' => 'require|array'
        ]);
        if ($validate !== true) {
            $this->fail($validate);
        }

        \think\Db::startTrans();
        try {
            $type = \app\common\model\Type::update($param, ['id' => $id], true);
            foreach ($param['spec'] as $k => $spec) {
                if (trim($spec['name']) == '') {
                    unset($param['spec'][$k]);
                    continue;
                }
                foreach ($spec['value'] as $i => $value) {
                    if (trim($value) == '') {
                        unset($param['spec'][$k]['value'][$i]);
                    }
                }
                if (empty($param['spec'][$k]['value'])) {
                    unset($param['spec'][$k]);
                }
            }
            $spec_data = [];
            foreach ($param['spec'] as $k => $spec) {
                $spec_data[] = [
                    'sort' => $spec['sort'],
                    'spec_name' => $spec['name'],
                    'type_id' => $type['id']
                ];
            }
            \app\common\model\Spec::destroy(['type_id' => $id]);
            $spec_info = (new \app\common\model\Spec())->saveAll($spec_data);

            $spec_value = [];
            foreach ($param['spec'] as $k => $spec) {
                foreach ($spec['value'] as $i => $value) {
                    $spec_value[] = [
                        'spec_id' => $spec_info[$k]['id'],
                        'spec_value' => $value,
                        'type_id' => $type['id']
                    ];
                }
            }
            \app\common\model\SpecValue::destroy(['type_id' => $id]);
            $spec_value = (new \app\common\model\SpecValue())->saveAll($spec_value);

            foreach ($param['attr'] as $k => $spec) {
                if (trim($spec['name']) == '') {
                    unset($param['attr'][$k]);
                    continue;
                }
                foreach ($spec['value'] as $i => $value) {
                    if (trim($value) == '') {
                        unset($param['attr'][$k]['value'][$i]);
                    }
                }
                
            }

            $attribue = [];
            foreach ($param['attr'] as $k => $attr) {
                $attribute[] = [
                    'attr_name' => $attr['name'],
                    'type_id' => $id,
                    'attr_values' => implode(',',$spec['value']),
                    'sort'=>$spec['sort'],
                ];
            }
            \app\common\model\Attribute::destroy(['type_id'=>$id]);
            (new \app\common\model\Attribute())->saveAll($attribute);

            \think\Db::commit();
            $info = \app\common\model\Type::field('id,type_name')->find($id);
            $this->ok($info);
        } catch (\Exception $ex) {
            \think\Db::rollBack();
            $msg = $ex->getMessage();
            $this->fial($msg);
        }
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $goods = \app\common\model\Goods::where('type_id', $id)->find();
        if ($goods) {
            $this->fail('正在使用');
        }

        \think\Db::startTrans();

        try {
            \app\common\model\Attribute::destroy(['type_id' => $id]);
            \app\common\model\Spec::destroy(['type_id' => $id]);
            \app\common\model\SpecValue::destroy(['type_id' => $id]);
            \app\common\model\Type::destroy($id);
            \think\Db::commit();
            $this->ok();
        } catch (\Exception $ex) {
            \think\Db::rollback();
            $this->fail('删除失败');
        }
    }
}
