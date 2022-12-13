<?php
namespace app\controller;
use app\model\Order;
use app\service\ServiceOrder;
use app\service\ServiceShopify;
use think\Exception;

class Api extends Base
{
    public function index(){
       return 'Hello!';
    }

    public function create_order(){
        $d = input();
        return (new ServiceOrder())->create_order($d);
    }

    public function create_payment(){
        $d = input();
        return (new ServiceOrder())->create_payment($d);
    }
    public function order_status(){
        $d = input();
        $order = Order::where('order_sn',$d['order_key'])->findOrEmpty()->toArray();
        $data = ['code'=>1,'data'=>$order];
        return $data;
    }

    public function call_payment(){
        $d = input();
        return (new ServiceOrder())->call_payment();
    }

    public function wlog(){
        $d = input();
        (new \app\service\Base())->xielog('woo--'.json_encode($d));
    }


}
