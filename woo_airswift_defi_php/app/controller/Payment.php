<?php
namespace app\controller;

use app\model\Order;
use think\facade\Cache;

class Payment extends Base
{

    public function index()
    {
        $d = input();
        $order = Order::where('order_sn',$d['order_key'])->findOrEmpty()->toArray();
        $data = ['code'=>1,'order'=>$order];
        if(in_array($order['status'],['success','closed'])){
            return view('pay', $data);
        }
        else{
            return view('', $data);
        }
    }

    public function pay()
    {
        $d = input();
        $order = Order::where('order_sn',$d['order_key'])->findOrEmpty()->toArray();
        $data = ['code'=>1,'order'=>$order];
        return view('', $data);
    }
}
