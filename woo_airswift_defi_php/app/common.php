<?php
// 应用公共文件

/**
 * curl 操作函数
 * $d 参数列表
 * url  请求网址url
 * do  请求方式 DELETE/PUT/GET/POST 默认为GET data不为空则POST
 * tz  跳转跟随 0不跟随 1跟随 默认1
 * data  请求数据 支持数组方式
 * ref  来路
 * llq  浏览器头
 * qt  其他header信息 多个用数组传递
 * cookie cookie文件路径或者cookie信息 当为文件时.txt结尾
 * time 超时时间 默认10
 * daili 为空不用代理 array('CURLOPT_PROXY','CURLOPT_PROXYUSERPWD')
 * headon  是否返回header信息 默认0不返回 1=返回
 * code  是否返回HTTP状态码 code=1开启=>将return信息为 ['状态码','获取到的内容']
 * nossl  0验证证书 1不验证证书 默认验证
 * to_utf8 返回结果转utf8 1是 2否 默认是
 * gzip     返回结果压缩 1是 2否 默认是
 */
if (!function_exists('chttp')) {
    function chttp($d = [])
    {
        $mrd = ['url' => '', 'do' => '', 'tz' => '', 'data' => '', 'ref' => '', 'llq' => '', 'qt' => '', 'cookie' => '', 'time' => '', 'daili' => [], 'headon' => '', 'code' => '', 'nossl' => '', 'to_utf8' => '', 'gzip' => '', 'port' => ''];
        $d = array_merge($mrd, $d);

        $url = $d['url'];
        if ($url == "") {
            exit("URL不能为空!");
        }
        $header = [];

        if ($d['llq']) {
            $header[] = "User-Agent:" . $d['agent'];
        } else {
            $header[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64)AppleWebKit/537.36 (KHTML, like Gecko)Chrome/63.0.3239.26 Safari/537.36';
        }
        if ($d['ref']) {
            $header[] = "Referer:" . $d['ref'];
        }

        $ch = curl_init($url);
        if ($d['port'] != '') {
            curl_setopt($ch, CURLOPT_PORT, intval($d['port']));
        }
        //cookie 文件/文本
        if ($d['cookie'] != "") {
            if (substr($d['cookie'], -4) == ".txt") {
                //文件不存在则生成
                if (!wjif($d['cookie'])) {
                    wjxie($d['cookie'], '');
                }
                $d['cookie'] = realpath($d['cookie']);
                curl_setopt($ch, CURLOPT_COOKIEJAR, $d['cookie']);
                curl_setopt($ch, CURLOPT_COOKIEFILE, $d['cookie']);
            } else {
                $cookie = 'cookie: ' . $d['cookie'];
                $header[] = $cookie;
            }
        }

        //附加头信息
        if ($d['qt']) {
            foreach ($d['qt'] as $v) {
                $header[] = $v;
            }
        }
        //代理
        if (count($d['daili']) == 2) {
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            curl_setopt($ch, CURLOPT_PROXY, $d['daili'][0]);
            curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $d['daili'][1]);
        }

        $postData = $d['data'];
        $timeout = $d['time'] == "" ? 10 : ints($d['time'], 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if ($d['gzip'] != "0") {
            curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        }

        //跳转跟随
        if ($d['tz'] == "0") {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        } else {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        }

        //SSL
        if (substr($url, 0, 8) === 'https://' || $d['nossl'] == "1") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        //请求方式
        if (in_array(strtoupper($d['do']), ['DELETE', 'PUT'])) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($d['do']));
        } else {
            //POST数据
            if (!empty($postData)) {
                if (is_array($postData)) {
                    $postData = http_build_query($postData);
                }
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            } //POST空内容
            elseif (strtoupper($d['do']) == "POST") {
                curl_setopt($ch, CURLOPT_POST, 1);
            }
        }
        if ($d['headon'] == "1") {
            curl_setopt($ch, CURLOPT_HEADER, 1);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //超时时间
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, (int)$timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int)$timeout);

        //执行
        $content = curl_exec($ch);
        if ($d['to_utf8'] != "0") {
            $content = to_utf8($content);
        }

        //是否返回状态码
        if ($d['code'] == "1") {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $content = [$httpCode, $content];
        }

        curl_close($ch);
        return $content;
    }
}

//编码自动转换
if (!function_exists('to_utf8')) {
    function to_utf8($data = '')
    {
        if (!empty($data)) {
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    $data[$key] = to_utf8($value);
                }
                return $data;
            } else {
                $fileType = mb_detect_encoding($data, ['UTF-8', 'GBK', 'LATIN1', 'BIG5']);
                if ($fileType != 'UTF-8') {
                    $data = mb_convert_encoding($data, 'utf-8', $fileType);
                }
            }
        }
        return $data;
    }
}


//统一返回方法
function rs($msg = '', $code = -1, $data = '', $qt = [])
{
    $rs = ['code' => $code, 'msg' => $msg];
    if ($data) {
        $rs['data'] = $data;
    }
    if (!empty($qt) && is_array($qt)) {
        $rs = array_merge($rs, $qt);
    }
    return $rs;
}

/**
 * 成功返回
 *
 * @param string $msg
 * @param array $data
 *
 * @return array
 * @author wumengmeng <wu_mengmeng@foxmail.com>
 */
function r_ok($msg = '', $data = [])
{
    return rs($msg, 1, $data);
}

/**
 * 失败返回
 *
 * @param        $msg
 * @param int $code
 * @param array $data
 *
 * @return array
 * @author wumengmeng <wu_mengmeng@foxmail.com>
 */
function r_fail($msg = '', $code = -1, $data = [])
{
    return rs($msg, $code, $data);
}

function filter_spaces($str = '')
{
    return str_replace(' ', '', $str);
}


//todo 999
function glwb($data)
{
    return $data;
}



function uid()
{
    return (new \SimonJWTToken\JWTToken())->userId();
}

function currency_conversion($currencyCode,$total_amount,$order_id = 0)
{
   /* // api.5
    $d1 = [
        'do'=>'GET',
        'url'=>"https://api.apilayer.com/exchangerates_data/convert?to=USD&from={$currencyCode}&amount={$total_amount}",
        'qt'=>[
            'apikey: vIc43zNe7qA5yVPpAb560Uo4wXnPhrdA',
            'Content-Type: text/plain'
        ]
    ];
    $res = json_decode(chttp($d1),true);
    if($res['success'] === true){
        return $res['result'];
    }
    else {
        (new \app\service\Base())->xielog("$order_id-----{$res['message']}");
        return r_fail('Currency exchange rate conversion failed!');
    }*/

    // api.7
    $d = [
        'do'=>'GET',
        'url'=>"https://marketdata.tradermade.com/api/v1/convert?api_key=2GGANIjul2_ZY6hPd_4c&from={$currencyCode}&to=USD&amount=1",
    ];
    $res = json_decode(chttp($d),true);
    if(isset($res['total'])){
        return $res['total'] * $total_amount;
    }
    else{
        (new \app\service\Base())->xielog("$order_id-----{$res['message']}");
        return r_fail('Currency exchange rate conversion failed!');
    }
/*
    // api.11
    $d = [
        'do'=>'POST',
        'url'=>'https://neutrinoapi.net/convert',
        'data'=>[
            'from-value'=>$total_amount,
            'from-type'=>$currencyCode,
            'to-type'=>"USD",
        ],
        'qt'=>[
            'user-id: 644577519@qq.com',
            'api-key: VzLCqZFwsJVqo2BlcICVMcP06u7PmLhsMT5YzlnDSUq3iHTL',
        ]
    ];
    $res = json_decode(chttp($d),true);
    if($res['valid'] === true){
        return $res['result'];
    }
    else{
        (new \app\service\Base())->xielog("$order_id-----{$res['message']}");
        return r_fail('Currency exchange rate conversion failed!');
    }*/
}

function float_rtrim0($value){
    $arrValue = explode('.',$value);
    $left = $arrValue[0];
    $right = rtrim($arrValue[1],"0");
    return $right === ''?$left:$left.'.'.$right;
}

function eth_format_num($num,$wei = 18){
    return float_rtrim0(bcdiv($num,pow(10,$wei),$wei));
}

