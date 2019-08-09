<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;

class Goods extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $param = input();
        $where = [];
        if (isset($param['keyword']) && !empty($param['keyword'])) {
            $keyword = $param['keyword'];
            $where['goods_name'] = ['like', "%$keyword%"];
        }
        $list = \app\common\model\Goods::with('cates,brands,types')->where($where)->paginate(10);
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
        $param = input();
        $validate = $this->validate($param, [
            'goods_name|商品名' => 'require',
            'goods_remark|商品简介' => 'require',
            'cate_id|商品分类id' => 'require|integer|gt:0',
            'brand_id|商品品牌id' => 'require|integer|gt:0',
            'goods_price|商品价格' => 'require|float',
            'market_price|市场价格' => 'require|float',
            'cost_price|成本价格' => 'require|float',
            'goods_logo|商品logo' => 'require',
            'is_free_shipping|是否包邮' => 'require|in:0,1',
            'mould_id|运费模板id' => 'integer|gt:0',
            'weight|商品重量' => 'float|gt:0',
            'volume|商品体积' => 'float|gt:0',
            'goods_number|总库存' => 'integer|egt:0',
            'is_hot|是否热卖' => 'in:0,1',
            'is_on_sale|是否上架' => 'in:0,1',
            'is_recommend|是否推荐' => 'in:0,1',
            'is_new|是否新品 ' => 'in:0,1',
            'sort|排序' => 'egt:0',
            'goods_images|相册图片集合' => 'require|array',
            'type_id|商品模型id' => 'require|integer|gt:0',
            'item|商品规格值集合' => 'require|array',
            'attr|商品属性值集合' => 'require|array'
        ]);
        if ($validate !== true) {
            $this->fail($validate);
        }
        \think\Db::startTrans();
        try {
            $logo = $param['goods_logo'];
            if (is_dir($logo)) {
                $path = dirname($logo) . DS . 'thumb_' . basename($logo);
                \think\Image::open('.' . $logo)->thumb(210, 240)->save('.' . $path);
                $param['goods_logo'] = $path;
            }
            $param['goods_attr'] = json_encode($param['attr'], JSON_UNESCAPED_UNICODE);
            $goods = \app\common\model\Goods::create($param, true);

            $goods_images = [];
            foreach ($param['goods_images'] as $k => $image) {
                if (is_file('.' . $image)) {

                    $path_small = dirname($image) . DS . 'thumb_small_' . basename($image);
                    $path_big = dirname($image) . DS . 'thumb_big_' . basename($image);
                    $img = \think\Image::open('.' . $image);
                    $img->thumb(800, 800)->save('.' . $path_big);
                    $img->thumb(400, 400)->save('.' . $path_small);
                }

                $goods_images[] = [
                    'goods_id' => $goods['id'],
                    'pics_big' => $path_big,
                    'pics_sma' => $path_small
                ];
            }
            (new \app\common\model\GoodsImages())->saveAll($goods_images);

            $goods_item = [];
            foreach ($param['item'] as $k => $item) {
                $goods_item[] = [
                    'goods_id' => $goods['id'],
                    'value_ids' => $item['value_ids'],
                    'price' => $item['price'],
                    'value_names' => $item['value_names'],
                    'cost_price' => $item['cost_price'],
                    'store_count' => $item['store_count']

                ];
            }
            (new \app\common\model\SpecGoods())->saveAll($goods_item);
            \think\Db::commit();
            $list = \app\common\model\Goods::with('brands,cates,types')->find($goods['id']);
            $this->ok($list);
        } catch (\Exception $ex) {
            \think\Db::rollback();
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
        $list = \app\common\model\Goods::with('type,type.specs,spec_goods,brand,category,goods_images')->find($id);
        $attrs = \app\common\model\Goods::with('type.attrs')->find($id);
        $spec_values = \app\common\model\Goods::with('type.specs.spec_values')->find($id);
        $spec_values['type']['attrs'] = $attrs['type']['attrs'];
        unset($list['type']);
        $list['type'] = $spec_values['type'];
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
        $goods = \app\common\model\Goods::with('type,spec_goods,brand,category,category.brands,goods_images')->find($id);
        $attrs = \app\common\model\Goods::with('type.attrs')->find($id);
        $spec_values = \app\common\model\Goods::with('type.specs.spec_values')->find($id);
        $spec_values['type']['attrs'] = $attrs['type']['attrs'];        
        unset($goods['type']);
        $goods['type'] = $spec_values['type'];

        $cate_one = \app\common\model\Category::where(['pid' => 0])->field('id,cate_name')->select();
        $cate_path = explode('_', $goods['category']['pid_path']);
        $cate_two = \app\common\model\Category::where(['pid' => $cate_path[1]])->field('id,cate_name')->select();
        $cate_three = \app\common\model\Category::where(['pid' => $cate_path[2]])->field('id,cate_name')->select();

        $type = \app\common\model\Type::field('id,type_name')->select();
        $list = [
            'goods' => $goods,
            'category' => [
                'cate_one' => $cate_one,
                'cate_two' => $cate_two,
                'cate_three' => $cate_three
            ],
            'type' => $type
        ];
        $this->ok($list);
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
        $param = $request->param();
        $validate = $this->validate($param, [
            'goods_name|商品名' => 'require',
            'goods_remark|商品简介' => 'require',
            'cate_id|商品分类id' => 'require|integer|gt:0',
            'brand_id|商品品牌id' => 'require|integer|gt:0',
            'goods_price|商品价格' => 'require|float',
            'market_price|市场价格' => 'require|float',
            'cost_price|成本价格' => 'require|float',
            'goods_logo|商品logo' => 'require',
            'is_free_shipping|是否包邮' => 'require|in:0,1',
            'mould_id|运费模板id' => 'integer|gt:0',
            'weight|商品重量' => 'float|gt:0',
            'volume|商品体积' => 'float|gt:0',
            'goods_number|总库存' => 'integer|egt:0',
            'is_hot|是否热卖' => 'in:0,1',
            'is_on_sale|是否上架' => 'in:0,1',
            'is_recommend|是否推荐' => 'in:0,1',
            'is_new|是否新品 ' => 'in:0,1',
            'sort|排序' => 'egt:0',
            'goods_images|相册图片集合' => 'require|array',
            'type_id|商品模型id' => 'require|integer|gt:0',
            'item|商品规格值集合' => 'require|array',
            'attr|商品属性值集合' => 'require|array'
        ]);
        if ($validate !== true) {
            $this->fail($validate);
        }
        \think\Db::startTrans();
        try {
            if (is_file($param['goods_logo'])) {
                \think\Image::open('.' . $param['goods_logo'])->thumb(210, 240)->save(dirname($param['goods_logo']) . 'thumb_' . basename($param['goods_logo']));
            }
            $param['goods_logo'] = dirname($param['goods_logo']) . 'thumb_' . basename($param['goods_logo']);
            $param['goods_attr'] = json_encode($param['attr'], JSON_UNESCAPED_UNICODE);
            \app\common\model\Goods::update($param, ['id' => $id], true);


            if (isset($param['goods_images'])) {
                $images = [];
                foreach ($param['goods_images'] as $k => $img) {
                    if (is_file('.' . $img)) {
                        $path_big = dirname($img) . 'thumb_big_' . basename($img);
                        $path_sma = dirname($img) . 'thumb_sma_' . basename($img);
                        $image = \think\Image::open('.'.$img);
                        $image->thumb(800,800)->save('.'.$path_big);
                        $image->thumb(400,400)->save('.'.$path_sma);
                    }
                    $images[] = [
                        'goods_id'=>$id,
                        'pics_big'=>$path_big,
                        'pics_sma'=>$path_sma,
                    ];
                }
                $images = (new \app\common\model\GoodsImages())->saveAll($images);
            }
            \app\common\model\SpecGoods::destroy(['goods_id'=>$id]);
            $items = [];
            foreach($param['item'] as $item){
                $item['goods_id'] = $id;
                $items[]=$item;
            }
            (new \app\common\model\SpecGoods())->allowField(true)->saveAll($items);
            \think\Db::commit();
            $list = \app\common\model\Goods::with('types,brands,cates')->find($id);
            $this->ok($list);
        } catch (\Exception $ex) { 
            \think\Db::rollback();
            $msg = $ex->getMessage();
            $this->fail($msg);
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
        $goods = \app\common\model\Goods::find($id);
        if(empty($goods)){
            $this->fail('数据异常');
        }
        if($goods['is_on_sale'] == 1){
            $this->fail('商品在卖，别删');
        }
        \app\common\model\Goods::destroy($id);
        $this->ok();
    }

    public function delpics($id){
        $img = \app\common\model\GoodsImages::find($id);
        if(empty($img)){
            $this->fail('数据异常');
        }
        $img->delete();
        unlink('.'.$img['pics_big']);
        unlink('.'.$img['pics_sma']);
        $this->ok();
    }
}
