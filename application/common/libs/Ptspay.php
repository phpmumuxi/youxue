<?php

namespace app\common\libs;


// use think\Model;

/**
 * pos机订单查询
 */
class Ptspay
{
    // 应用接入ID
    private $appId = 'ee2eb51c51c1416291f351b5355410aa';
    // 商户号
    private $merchNo = '898320156410609';

    //MP系统提供编号
    private $keyNO = '87ae4b91e86648e5a24348926c7477e0';
    // private $keyNO = '22193e4bf5ed4239b683314eaa65db6b';

    //接口ID，订单查询传03
    private $intId = '03';

    // 调用的接口版本(固定值)
    private $version = '1.0';

    // 请求地址*****测试环境地址
    // private $url = 'http://210.22.91.77:7001/pts/thirdService';
    private $url = 'https://180.166.112.26:8443/pts/thirdService';

    // 公钥
    private $publicKey='MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCykqnbhk0nkEqMV1wFRO2D
9E8r4WUhB6jh9//YWj+xQv63quld9633q9cZ7j1AAdabUrg9tvecQl0g+wXx
c+Bo2pX/Qmz6xd48UbeZXTzcLbQTJMQ4th6s5p8JmmmQXxQkGQJ8MQwEQ8Iu
ePq45MCStl5vTq4OgP2zGRxAkzXujwIDAQAB';
// private $publicKey='MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCwAnUPcE7LXnOM1QKVwC0ooFXTJlPxvvSabx3oitI/OCQg3h3Ng3VqkhCqMtsyFUsbQVFt4mwhhc7axg9isHFSBr09Z4tof1qV/P0fLBpL5dRVjA6XE+MtS0nd8l+er0nevCPtVRxYdIB+zbVu2LP+qLR/XcVu3uqrlnGwtgImnQIDAQAB';

    //私钥
    private $privateKey = 'MIICdQIBADANBgkqhkiG9w0BAQEFAASCAl8wggJbAgEAAoGBAIMJ79ge5a6t
enIthSUdyRgXkwu4Q4kyNHvIb0Fg7q/FejL/UqoLNkD9WdXwPxA9SIQlD7No
QbYB7D3KKBNPNfI82CdrG6+sK0VGGziJcW8Tt3SEtvTpkp+0GmsdQhmnC75t
7gabMF1Esxf0gLPH4NrXcz8Kwt4xCA3oYAd9xKOFAgMBAAECgYBX4utFJXvg
yyNLvvIE8dQ032LVQnGxa8W9m7HaxJtxsl4CJDKaL8jkj8eX8HkreB7R2lWM
e4lO/D0pOBPm14KTCRzZyu+ksU3JiMDcU0dhCIjDQXUEoGMnAWUnBbzLrjut
H9PO4lsMw/HqKjwtAfYwqEObqVYcptBYjvkXX+skoQJBAOLcgTBrBcEkEavb
8dq39rAGJd1cCiPL76DtZ4fhctY0LX+zIYXkBQb4DnrBMpGKtOHwG+OKpYob
AtV5XNhiFQkCQQCT3qnECEpBxSIsbkVHMMUlcIhLQCkWpsHwrzLS4CyteFDr
nYu49PblgihoHc1MSVrYa0yvegXBXhfrhq1LxBWdAkBHdDB0saDUXqn6OYKJ
41udwbH3cN/4umk1hNERKV9kPHcAO8mZRGKY+VSGMYfd85RZfakrGCZfw3Y3
CszFks8RAkBBO8nJIZ8gxMb+sub9MRbIHY0DJZr60zZb5+6T+TG8lYedajNp
tSf2uCT7Sap4LSRWtX9vKnW/iLMkxJVPB4JBAkA3YJhTuMPgUfwfvyjYixjM
eKU4w2JWjbakYaekscKkL5tyiWIXcXzHqmdMT+dVym6lkRCgIzMbMGQieuXe
1YgO';


    /*
        $arr['order_no'] 订单号
        $arr['orderId'] 银行订单号
     */
    public function sign($arr)
    {
        $array=[
            'intId'=>$this->intId,
            'orderInfos'=>[
                [
                'extOrderId' => $arr['order_no'],
                'merchNo' => $this->merchNo,
                'appId' => $this->appId,
                // 'orderId' => $arr['orderId'],
                ]
            ]
        ];

        $biz_content = json_encode($array);

        $data=[
            'MD5'=>$this->md5String($biz_content,$this->keyNO),
            'requestJson'=>$array
        ];
        $params = [
            'NO'=>$this->keyNO,
        ];
       

// return  $data;
        // 获取加密码
        $params['Body'] = $this->encrypt($data);
        // return  $params['Body'];
        $params = json_encode($params);
 
        $re = $this->postxml($this->url, $params);
        return $re;
    }
    //发送请求
    public function postxml($url,$str){

        if (!extension_loaded("curl")) {
            $this->error('curl error',2);
        }
        $header = [
            'Content-Type: application/json'
        ];
        //构造xml
        $xmldata=$str;
        //初始一个curl会话
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        //设置url
        curl_setopt($curl, CURLOPT_URL,$url);
        //设置发送方式：
        curl_setopt($curl, CURLOPT_POST, true);
        //https 不验证ssl证书
        curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置发送数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xmldata);
        //抓取URL并把它不传递给浏览器
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //存取数据
        $file_contents = curl_exec($curl);
        //关闭cURL资源，并且释放系统资源

        if(curl_error ( $curl ))
        { echo  'Curl error: '  .  curl_error ( $curl );}
        curl_close($curl);

        return $file_contents;

    }

    public function encrypt($arr)
    {

        $public_key=str_replace("\n",'', $this->publicKey);

        // $public_key = wordwrap($public_key, 64, "\n", true);
        $public_key = "-----BEGIN PUBLIC KEY-----\n" . $public_key;
        $public_key = $public_key ."\n-----END PUBLIC KEY-----";


        //这个函数可用来判断公钥是否是可用的
        $pu_key = openssl_pkey_get_public($public_key);

        $data = json_encode($arr);//原始数据

        $ptext = null;
        $crypto = '';
        foreach (str_split($data, 117) as $chunk) {
            if (!openssl_public_encrypt($chunk, $ptext, $pu_key)) {
                echo "<br/>" . openssl_error_string() . "<br/>";
            }

            $crypto .= $ptext;
        }
        return bin2hex($crypto);
    }

    public function md5String($str,$no)
    {
        $sb=$no;
        $length=strlen($str);
        $i = 1;
        $j = 1;
        while ($i < $length)
        {
          $sb.=$str[$i];
          $p = $i;
          $i += $j;
          $j = $p;
        }
        return md5($sb.$str);
    }

    //解密
    public function decodePos($str)
    {
        $string=""; 
        for($i=0;$i<strlen($str)-1;$i+=2){
            $string.=chr(hexdec($str[$i].$str[$i+1]));
        }
        mb_detect_encoding($string,'utf-8');
        $privateKey = $this->privateKey;
        $privateKey=str_replace("\n",'', $privateKey);

        $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" . $privateKey;
        $privateKey = $privateKey ."\n-----END RSA PRIVATE KEY-----";

        //这个函数可用来判断私钥是否是可用的，可用返回
        $pi_key = openssl_pkey_get_private($privateKey);
        $crypto = '';

        foreach (str_split($string, 128) as $chunk) {
            openssl_private_decrypt($chunk, $decrypted, $pi_key);
            $crypto .= $decrypted;
        }
        return $crypto;

    }



}