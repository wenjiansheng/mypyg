<?php

namespace app\home\controller;

use think\Controller;
use think\Request;

class Order extends Base
{

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        if (!session('?user_info')) {
            session('back_url', 'home/cart/index');
            $this->redirect('home/login/login');
        }

        $user_id = session('user_info.id');
        $address = \app\common\model\Address::where('user_id', $user_id)->select();

        // $info = \app\common\model\Cart::with('goods,spec_goods')->where('user_id',$user_id)->where('is_selected',1)->select();
        // $cart_data = (new \think\Collection($info))->toArray();
        // $total_number = 0;
        // $total_price = 0;
        // foreach($cart_data as &$v){
        //     if(isset($v['price']) && $v['price'] > 0){
        //         $v['goods_price'] = $v['price'];
        //     }
        //     if(isset($v['cost_price2']) && $v['cost_price2'] > 0){
        //         $v['cost_price'] = $v['cost_price2'];
        //     }
        //     if(isset($v['store_count']) && $v['store_count'] > 0){
        //         $v['goods_number'] = $v['store_count'];
        //     }
        //     if(isset($v['store_frozen']) && $v['store_frozen'] > 0){
        //         $v['frozen_number'] = $v['store_frozen'];
        //     }
        //     $total_number += $v['number'];
        //     $total_price += $v['number'] * $v['goods_price'];
        // }
        // unset($v);
        $res = \app\home\logic\OrderLogic::getCartDataWithGoods();
        $cart_data = $res['cart_data'];
        $total_number = $res['total_number'];
        $total_price = $res['total_price'];
        return view('getOrderInfo', ['add' => $address, 'cart_data' => $cart_data, 'total_number' => $total_number, 'total_price' => $total_price]);
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
            'address_id' => 'require|integer|gt:0'
        ]);
        if ($validate !== true) {
            $this->error($validate);
        }
        $address = \app\common\model\Address::find($param['address_id']);
        if (!$address) {
            $this->error('请重新选择收货地址');
        }
        $order_sn = time() . mt_rand(100000, 999999);
        $user_id = session('user_info.id');
        $res = \app\home\logic\OrderLogic::getCartDataWithGoods();
        $order_data = [
            'user_id' => $user_id,
            'order_sn' => $order_sn,
            'consignee' => $address['consignee'],
            'address' => $address['area'] . $address['address'],
            'phone' => $address['phone'],
            'goods_price' => $res['total_price'],
            'shipping_price' => 0,
            'coupon_price' => 0,
            'order_amount' => $res['total_price'],
            'total_amount' => $res['total_price']
        ];
        \think\Db::startTrans();
        try {
            foreach ($res['cart_data'] as $v) {
                if ($v['goods_number'] < $v['number']) {
                    throw new \Exception('订单中包含库存不足的商品');
                }
            }

            $order = \app\common\model\Order::create($order_data, true);
            $order_goods_data = [];
            foreach ($res['cart_data'] as $v) {
                $row = [
                    'order_id' => $order['id'],
                    'goods_id' => $v['goods_id'],
                    'spec_goods_id' => $v['spec_goods_id'],
                    'number' => $v['number'],
                    'goods_name' => $v['goods_name'],
                    'goods_logo' => $v['goods_logo'],
                    'goods_price' => $v['goods_price'],
                    'spec_value_names' => $v['value_names'],
                ];
                $order_goods_data[] = $row;
            }
            (new \app\common\model\OrderGoods())->saveAll($order_goods_data);

            $goods = [];
            $spec_goods = [];
            foreach ($res['cart_data'] as $v) {
                if ($v['spec_goods_id']) {
                    $spec_goods[] = [
                        'id' => $v['spec_goods_id'],
                        'store_count' => $v['goods_number'] - $v['number'],
                        'store_frozen' => $v['frozen_number'] + $v['number'],

                    ];
                } else {
                    $goods[] = [
                        'id' => $v['goods_id'],
                        'goods_number' => $v['goods_number'] - $v['number'],
                        'frozen_number' => $v['frozen_number'] + $v['number'],
                    ];
                }
            }

            (new \app\common\model\Goods())->saveAll($goods);
            (new \app\common\model\SpecGoods())->saveAll($spec_goods);
            \think\Db::commit();
            $url = url('home/order/qrpay', ['id' => $order->order_sn, 'debug' => 'true'], true, "http://pyg.tbyue.com");
            $qrCode = new \Endroid\QrCode\QrCode($url);
            $qr_path = '/uploads/qrcode/' . uniqid(mt_rand(100000, 999999), true) . '.png';
            $qrCode->writeFile('.' . $qr_path);
            $this->assign('qr_path', $qr_path);
            $pay_type = config('pay_type');
            return view('pay/pay', ['order_sn' => $order_sn, 'pay_type' => $pay_type, 'total_price' => $res['total_price']]);
        } catch (\Exception $ex) {
            \think\Db::rollback();
            $msg = $ex->getMessage();
            $this->error($msg);
        }
    }

    /**
     *支付回调
     */
    public function callback()
    {
        $param = input();
        require_once("./plugins/alipay/config.php");
        require_once './plugins/alipay/pagepay/service/AlipayTradeService.php';
        $alipayService = new \AlipayTradeService($config);
        $result = $alipayService->check($param);
        if ($result) {
            $order_sn = $param['out_trade_no'];
            $order = \app\common\model\Order::where('order_sn', $order_sn)->find();
            return view('pay\paysuccess', ['pay_name' => '支付宝', 'order_amount' => $param['total_amount'], 'order' => $order]);
        } else {
            return view('pay\payfail', ['msg' => '支付失败']);
        }
    }

    /**
     * 订单提交
     */
    public function pay()
    {
        $param = input();
        $validate = $this->validate($param, [
            'order_sn' => 'require',
            'pay_code' => 'require'
        ]);
        if ($validate !== true) {
            $this->error($validate);
        }

        $user_id = session('user_info.id');
        $order = \app\common\model\Order::where('order_sn', $param['order_sn'])->where('user_id', $user_id)->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        $order->pay_code = $param['pay_code'];
        $order->pay_name = config('pay_type' . $param['pay_code'])['pay_name '];
        $order->save();

        switch ($param['pay_code']) {
            case 'wechat':
                break;
            case 'union':
                break;
            case 'alipay':
            default:
                echo "<form id='alipayment' action='/plugins/alipay/pagepay/pagepay.php' method='post' style='display:none'>
            <input id='WIDout_trade_no' name='WIDout_trade_no' value='{$order['order_sn']}'/>
            <input id='WIDsubject' name='WIDsubject' value='品优购订单' />
            <input id='WIDtotal_amount' name='WIDtotal_amount' value='{$order['order_amount']}'/>
            <input id='WIDbody' name='WIDbody' value='品优购订单，测试订单，你付款了我也不发货' />
        </form><script>document.getElementById('alipayment').submit();</script>";
                break;
        }
    }

    /**
     * 
     */
    public function notify()
    {
        //接收参数
        $params = input();
        //记录日志
        trace('支付宝异步通知-home/order/notify:' . json_encode($params), 'debug');
        //参考 /plugins/alipay/notify_url.php
        //参数检测（签名验证）  接收到的参数 和 支付宝传递的参数 是否发生改变
        require_once("./plugins/alipay/config.php");
        require_once './plugins/alipay/pagepay/service/AlipayTradeService.php';
        $alipaySevice = new \AlipayTradeService($config);
        $result = $alipaySevice->check($params);
        if (!$result) {
            //验证签名失败
            //记录日志
            trace('支付宝异步通知-home/order/notify:验签失败', 'error');
            echo 'fail';
            die;
        }
        //验签成功
        $order_sn = $params['out_trade_no'];
        $trade_status = $params['trade_status'];
        if ($trade_status == 'TRADE_FINISHED') {
            //交易已经处理过
            echo 'success';
            die;
        }
        //交易尚未处理
        $order = \app\common\model\Order::where('order_sn', $order_sn)->find();
        if (!$order) {
            //订单不存在
            //记录日志
            trace('支付宝异步通知-home/order/notify:订单不存在', 'error');
            echo 'fail';
            die;
        }
        if ($order['order_amount'] != $params['total_amount']) {
            //支付金额不对
            //记录日志
            trace('支付宝异步通知-home/order/notify:支付金额不对', 'error');
            echo 'fail';
            die;
        }
        //修改订单状态
        if ($order['order_status'] == 0) {
            $order->order_status = 1;
            $order->pay_time = time();
            $order->save();
            //记录支付信息 核心字段 支付宝订单号
            $json = json_encode($params);
            //添加数据到 pyg_pay_log表  用于后续向支付宝发起交易查询
            \app\common\model\PayLog::create(['order_sn' => $order_sn, 'json' => $json]);
            echo 'success';
            die;
        }
        echo 'success';
        die;
    }


    public function qrpay()
    {
        $agent = request()->server('HTTP_USER_AGENT');
        //判断扫码支付方式
        if ( strpos($agent, 'MicroMessenger') !== false ) {
            //微信扫码
            $pay_code = 'wx_pub_qr';
        }else if (strpos($agent, 'AlipayClient') !== false) {
            //支付宝扫码
            $pay_code = 'alipay_qr';
        }else{
            //默认为支付宝扫码支付
            $pay_code = 'alipay_qr';
        }
        //接收订单id参数
        $order_sn = input('id');
        //创建支付请求
        $this->pingpp($order_sn,$pay_code);
    }


    public function pingpp($order_sn,$pay_code)
    {
        //查询订单信息
        $order = \app\common\model\Order::where('order_sn', $order_sn)->find();
        //ping++聚合支付
        \Pingpp\Pingpp::setApiKey(config('pingpp.api_key'));// 设置 API Key
        \Pingpp\Pingpp::setPrivateKeyPath(config('pingpp.private_key_path'));// 设置私钥
        \Pingpp\Pingpp::setAppId(config('pingpp.app_id'));
        $params = [
            'order_no'  => $order['order_sn'],
            'app'       => ['id' => config('pingpp.app_id')],
            'channel'   => $pay_code,
            'amount'    => $order['order_amount'],
            'client_ip' => '127.0.0.1',
            'currency'  => 'cny',
            'subject'   => 'Your Subject',//自定义标题
            'body'      => 'Your Body',//自定义内容
            'extra'     => [],
        ];
        if($pay_code == 'wx_pub_qr'){
            $params['extra']['product_id'] = $order['id'];
        }
        //创建Charge对象
        $ch = \Pingpp\Charge::create($params);
        //跳转到对应第三方支付链接
        $this->redirect($ch->credential->$pay_code);die;
    }


    public function status()
    {
        //接收订单编号
        $order_sn = input('order_sn');
        //查询订单状态
        /*$order_status = \app\common\model\Order::where('order_sn', $order_sn)->value('order_status');
        return json(['code' => 200, 'msg' => 'success', 'data'=>$order_status]);*/
        //通过线上测试
        $res = curl_request("http://pyg.tbyue.com/home/order/status/order_sn/{$order_sn}");
        echo $res;die;
    }

    public function payresult()
    {
        $order_sn = input('order_sn');
        $order = \app\common\model\Order::where('order_sn', $order_sn)->find();
        if(empty($order)){
            return view('pay/payfail', ['msg' => '订单编号错误']);
        }else{
            return view('pay/paysuccess', ['pay_name' => $order->pay_name, 'order_amount'=>$order['order_amount'], 'order' => $order]);
        }
    }
    
   
}
