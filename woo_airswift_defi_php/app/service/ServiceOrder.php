<?php

namespace app\service;

use app\model\Order;
use think\facade\Cache;
use think\facade\Request;

class ServiceOrder extends Base
{
    public function create_order($d=[]){
        $d['currency_code'] = strtoupper($d['currency_code']??'');
        if(empty($d['app_key'])){
            return r_fail('The app_key cannot be empty!');
        }
        if(empty($d['order_id'])){
            return r_fail('The order_id cannot be empty!');
        }
        if(empty($d['currency_code'])){
            return r_fail('The currency_code cannot be empty!');
        }
        if(empty($d['amount'])){
            return r_fail('The amount cannot be empty!');
        }
        if(empty($d['callback_url'])){
            return r_fail('The callback_url cannot be empty!');
        }

        //Currency exchange rate conversion, all currencies are converted to USD
        $currency_code = strtoupper($d['currency_code']);
        $usd_amount = $d['amount'];
        if(strtolower($currency_code) !== 'usd'){
            //all currencies are converted to USD
            $res = currency_conversion($currency_code,$d['amount']);
            if(isset($res['code']) && $res['code'] === -1){
                return $res;
            }
            $usd_amount = $res;
        }

        //check order
        $usd_amount = ceil($usd_amount * 100)/100;
        $order_sn = md5("woo_{$d['app_key']}_{$d['order_id']}");
        $order = Order::where('order_sn', $order_sn)->findOrEmpty()->toArray();
        if(empty($order)){
            //create order
            $data = [
                'order_sn'=>$order_sn,
                'source'=>'woocommerce',
                'app_key'=>$d['app_key'],
                'order_id'=>$d['order_id'],
                'currency_code'=>$d['currency_code'],
                'amount'=>$d['amount'],
                'callback_url'=>$d['callback_url'],
                'usd_amount'=>$usd_amount,
                'is_call'=>3,
                'create_time'=>time(),
            ];
            $rows = Order::strict(false)->insert($data);
        }
        else{
            //update order
            //已成功或者取消的订单不能再更新
            if(in_array($order['status'],['success','closed'])){
                return r_ok('ok', Request::instance()->domain()."/payment?order_key=$order_sn");
            }
            $data = [
                'source'=>'woocommerce',
                'app_key'=>$d['app_key'],
                'order_id'=>$d['order_id'],
                'currency_code'=>$d['currency_code'],
                'amount'=>$d['amount'],
                'usd_amount'=>$usd_amount,
                'callback_url'=>$d['callback_url'],
                'status'=>'',
                'is_call'=>3,
                'payment_create_time'=>0,
                'payment_num'=>'',
                'crypto_currency'=>'',
                'crypto_amount'=>'',
                'collection_address'=>'',
                'payment_json'=>null,
                'update_time'=>time(),
            ];
            $rows = Order::where('order_sn', $order_sn)->update($data);
        }
        if($rows === 1){
            return r_ok('ok', Request::instance()->domain()."/payment?order_key=$order_sn");
        }
        else{
            return r_fail('Failed to create transaction!');
        }
    }

    public function create_payment($d=[]){
        $d['crypto_currency'] = strtoupper($d['cryptocurrency']??'');
        if(empty($d['crypto_currency'])){
            return r_fail('The crypto_currency cannot be empty!');
        }
        if(empty($d['order_key'])){
            return r_fail('The order_key cannot be empty!');
        }

        //判断，已创建payment的直接跳转到支付页面，没有创建payment的先创建，再跳转支付页
        //已成功的或者失败的payment不能再更新payment状态，其他创建新的payment并更新

        //query order
        $order_sn = $d['order_key'];
        $order = Order::where('order_sn', $order_sn)->findOrEmpty()->toArray();
        if($order['status'] === 'created' && $order['payment_create_time'] > 0){
            return r_ok('ok');
        }
        $amount_in_cent = intval($order['usd_amount']*100);
//        $amount_in_cent = ceil($order['usd_amount']*100);
        if(empty($order)){
            return r_fail('The order not exist!');
        }
       $url = "http://34.221.50.218:23456/open_api/payment";
        $data1  = [
            'access_key' => $order['app_key'],
            'title' => 'test payment',
            'desc' => 'create test airswift defi payment',
            'amount_in_cent' => $amount_in_cent,
            'chain_id' => 1,
            'currency' =>$d['crypto_currency'],
        ];
        $php_result = json_decode(curl_api($url,'POST',$data1),true);
        if ($php_result['success'] !== true || empty($php_result['msg'])) {
            return r_fail('Failed to create payment!');
        }
        $payment = $php_result['msg'];
        $data = [
            'payment_num'=>$payment['payment_num'],
            'crypto_currency'=>$d['crypto_currency'],
            'crypto_amount'=>eth_format_num($payment['friendly_amount'],$payment['currency_decimal_count']),
            'collection_address'=>$payment['collection_address'],
            'status'=>$payment['status'],
            'payment_json'=>json_encode($payment),
            'is_call'=>2,
            'payment_create_time'=>time(),
            'update_time'=>time(),
        ];
        $rows = Order::where('order_sn', $order_sn)->update($data);
        if($rows === 1){
            return r_ok('ok');
        }
        else{
            return r_fail('Failed to create transaction!');
        }
    }

    public function call_payment(){
        $orders = Order::where('source', 'woocommerce')->where('is_call', 2)->select()->toArray();
        foreach ($orders as $vv){
            if($vv['payment_num'] !== ''){
                $url = "http://34.221.50.218:23456/open_api/payment?payment_num={$vv['payment_num']}";
                $d1 = [
                    'do'=>'GET',
                    'url'=>$url
                ];
                $php_result = json_decode(chttp($d1),true);
                if ($php_result['success'] !== true || empty($php_result['msg'])) {
                    return r_fail('Failed to query payment!');
                }
                $payment_order = $php_result['msg'];
                if(in_array($payment_order['status'],['success','closed'])){
                    //call callbackurl
                    $d2 = [
                        'do'=>'get',
                        'url'=>$vv['callback_url'],
                        'data'=>json_encode([
                            'order_id'=>$vv['order_id'],
                            'status'=>$payment_order['status'],
                        ]),
                        'qt'=>[
                            'Content-Type: application/json'
                        ]
                    ];
                    $php_result = strtolower(chttp($d2));
                    if($php_result === 'success'){
                        $data = [
                            'payment_json'=>json_encode($payment_order),
                            'status'=>$payment_order['status'],
                            'is_call'=>1,
                            'update_time'=>time(),
                        ];
                        $rows = Order::where('id', $vv['id'])->update($data);
                    }

                }
            }

        }
    }

}