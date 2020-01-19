<?php

namespace app\common\libs;

/**
 * 银行卡信息
 */

class BankInfo{

    public function bankCard($code)
    {

    $data = [
        '_input_charset' => 'utf-8',
        'cardNo' => $code,
        'cardBinCheck' => "true"
    ];

    $res = $this->bankCurl($data);
    if(!$res)return false;

    $res = json_decode($res,true);
    if($res['validated']){
        $data = [
            'bankCode' => $res['bank'],
            'bankCard' => $res['key'],
            'bankType' => $res['cardType'],
            'bankTypeCode' => $this->cardType($res['cardType'])
        ];
    }else{
        $data = 1;
    }
    return $data;

    }

    private function cardType($value)
    {
        $arr = [
            'DC' => '储蓄卡',
            'CC' => '信用卡',
            'SCC' => '准贷记卡',
            'PC' => '预付费卡'
        ];
        if(array_key_exists($value,$arr)){
            return $arr[$value];
        }else{
            return false;
        }
    }

    private function bankCurl($arr)
    {
        $url = "https://ccdcapi.alipay.com/validateAndCacheCardInfo.json";
        $str = http_build_query($arr);
        $url = $url.'?'.$str;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,$url);
        //定义是否显示状态头 1：显示 ； 0：不显示
        curl_setopt($curl, CURLOPT_HEADER,0);
        // 获取数据返回
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //https 不验证ssl证书
        curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        //强制协议为1.0
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        //强制使用IPV4协议解析域名
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
        //存取数据
        $file_contents = curl_exec($curl);
        // if(curl_error ( $curl ))
        // { echo  'Curl error: '  .  curl_error ( $curl );}
        //关闭cURL资源，并且释放系统资源
        curl_close($curl);

        return $file_contents;
    }
}