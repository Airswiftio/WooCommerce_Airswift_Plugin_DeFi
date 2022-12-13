<?php

namespace app\service;
use think\facade\Db;

error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);
class Base
{
    public function xielog($nr){
        if(is_array($nr) || is_object($nr)){
            $nr = json_encode($nr);
        }
        Db::table('asp_log')->save(['nr'=>$nr]);
    }
}