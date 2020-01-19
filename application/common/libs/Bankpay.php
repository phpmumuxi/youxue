<?php

namespace app\common\libs;


// use think\Model;

/**
 * 银行卡app支付
 */
class Bankpay
{
    // 加密字符串
    public $key = '580749wyouxueRZC';
    // 商户分行号
    public $branchNo='0025';
    // 商户号
    // public $merchantNo='000005';
    public $merchantNo='630052';

    public $query = [
        'version' => '1.0',
        'charset' => 'UTF-8',
        'signType' => 'SHA-256'
    ];

    //支付接口url
    // public $api = 'http://121.15.180.66:801/NetPayment/BaseHttp.dll?MB_EUserPay';
    public $api = 'https://netpay.cmbchina.com/netpayment/BaseHttp.dll?MB_EUserPay';

    // 签约接口url
    // public $signUrl = 'http://121.15.180.66:801/mobilehtml/DebitCard/M_NetPay/OneNetRegister/NP_BindCard.aspx';
    public $signUrl = 'https://mobile.cmbchina.com/mobilehtml/DebitCard/M_NetPay/OneNetRegister/NP_BindCard.aspx';

    // 查询招行公钥url
    // public $doBusiness = 'http://121.15.180.72/CmbBank_B2B/UI/NetPay/DoBusiness.ashx';
    public $doBusiness = 'https://b2b.cmbchina.com/CmbBank_B2B/UI/NetPay/DoBusiness.ashx';

    // 签约回调
    public $noticeUrl = '/back/banksign';




    // $arr['agr_no'] 客户协议号
    // $arr['money'] 商品价格
    // $arr['back_url'] 当前回调链接
    // $arr['order_no'] 订单号
    // 工行支付接口
    public function sign($arr)
    {

        $req['dateTime'] = date('YmdHis',time());
        $req['branchNo'] = $this->branchNo;
        $req['merchantNo'] = $this->merchantNo;

        $req['date'] = date('Ymd',time());
        $req['payNoticePara'] = $arr['order_no'];
        $req['orderNo'] = $this->orderNo();
        $req['amount'] = sprintf("%.2f",0.1);
        // $req['amount'] = $arr['money'].'.00';
        $req['payNoticeUrl'] = $arr['back_url'];
        $req['agrNo'] = $arr['agr_no'];
        $req['signNoticeUrl'] = 'http://api.jsrzc.cn/api' . $this->noticeUrl;
        // $req['noticeUrl'] = URL_PATH . $this->noticeUrl;
        $req['merchantSerialNo'] = $this->merchantSerialNo();

        $arr = [
            "expireTimeSpan"=>"",
            "returnUrl"=>"",
            "clientIP"=>"120.23.16.22",
            "cardType"=>"",
            "lat"=>"",
            "lon"=>"",
            "merchantBridgeName"=>"",
            "merchantCssUrl"=>"",
            "userID"=>"",
            "mobile"=>"",
            "riskLevel"=>"",
            "signNoticePara"=>"",
        ];


        $req = array_merge($arr,$req);
        $str = $this->jsonRequestData($req);
        $param_request = $str;

        $sub_url = $this->api;

$html = <<<eot
        <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        </head>
        <body>
        <body onload="javascript:document.pay_form.submit();">

            <form id="pay_form" name="pay_form" action="$sub_url" method="post">

eot;
        foreach ($param_request as $key => $value) {
            $html .= "   <input type='hidden' name='{$key}' id='{$key}' value='{$value}' />\n";
        }

$html .= <<<eot
        <input type="submit" value="submit" style="display:none" />
         <!--<input type="submit" style="display:none" type="hidden">-->
        </form>
    </body>
    </html>
eot;

        echo $html;

        exit;
        // return $file;

    }

    // $arr['agr_no'] 客户协议号
    // 工行签约
    public function signPhone($arr)
    {

        $req = [
            'dateTime' =>date('YmdHis',time()),
            'merchantSerialNo'=>$this->merchantSerialNo(),
            'agrNo'=>$arr['agr_no'],
            'branchNo'=>$this->branchNo,
            'merchantNo'=>$this->merchantNo,
            'noticeUrl'=>URL_PATH . $this->noticeUrl
        ];

        $str = $this->jsonRequestData($req);

        $file = $this->postxml($str,$this->signUrl);

        return $file;
    }

    // 获取招行公钥
    public function selectNetPay()
    {
        $req = [
            'dateTime' =>date('YmdHis',time()),
            'txCode'=>'FBPK',
            'branchNo'=>$this->branchNo,
            'merchantNo'=>$this->merchantNo,
        ];
        $str = $this->jsonRequestData($req);

        $file = $this->postxml($str,$this->doBusiness);

        $file = json_decode($file,true);

        if($file['rspData']['rspCode'] !='SUC0000'){
            // $this->errorMsg('bankApiError');
            return false;
        }
        return $file['rspData']['fbPubKey'];
    }

    // 获取加密码
    public function jsonRequestData($arr)
    {
        $query = $this->query;
        $data=$this->buildQuery($arr);
        $query['sign']=$data;
        $query['reqData'] = $arr;
        $re = ['jsonRequestData'=>json_encode($query)];

        return $re;
    }

    /**
     * [postxml 发送支付请求]
     * @param  [string] $str [请求的xml]
     * @return [string]      [响应数据]
     */
    public function postxml($str,$url){

        if (!extension_loaded("curl")) {
            $this->error('curl error',2);
        }
        //构造xml
        $xmldata=$str;
        //初始一个curl会话
        $curl = curl_init();
        //设置url
        curl_setopt($curl, CURLOPT_URL,$url);
        //设置发送方式：
        curl_setopt($curl, CURLOPT_POST, true);
        //设置发送数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xmldata);
        // //https 不验证ssl证书
        // curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, FALSE); 
        // curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        //抓取URL并把它不传递给浏览器
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //抓取URL并把它不传递给浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        //存取数据
        $file_contents = curl_exec($curl);
        if(curl_error ( $curl ))
        { echo  'Curl error: '  .  curl_error ( $curl );}
        //关闭cURL资源，并且释放系统资源
        curl_close($curl);

        return $file_contents;

    }

    public function merchantSerialNo()
    {
        $str=rand('0','999999');
        return time().$str;
    }

    public function orderNo()
    {
        $str = '1'.date('His').rand('100','999');
        return $str;
    }

    /**
     * [buildQuery 加密数据]
     * @param  [array] $query [加密的数组]
     * @return [string]       [加密后字符串]
     */
    public function buildQuery($query)
    {

        if (!$query) {
            return null;
        }

        //将要 参数 排序
        ksort( $query );

        //重新组装参数
        $params = array();
        foreach ($query as $key => $value) {
            $params[] = $key .'='. $value;
        }

        $data = implode('&', $params);

        $data .= '&'.$this->key;

        $sign = strtoupper(hash('sha256', mb_convert_encoding($data,"UTF-8")));

        return $sign;

    }

    public function solutionSign($query)
    {
        $arr = $query['noticeData'];
        // 返回的签名结果
        $sig_dat = $query['sign'];

        // 待验证签名字符串

        ksort($arr);
        foreach ($arr as $key => $value) {
            $params[] = $key .'='. $value;
        }
        $toSign_str = implode('&', $params);

        // 获取公钥
        $a = new \app\api\model\BankAll();
        $pub_key = $a->getOnlineKey(1);


        // 处理公钥
        $pem = chunk_split($pub_key, 64, "\n");
        $pem = "-----BEGIN PUBLIC KEY-----\n" . $pem . "-----END PUBLIC KEY-----\n";
        $pkid = openssl_pkey_get_public($pem);

        if (empty($pkid)) {
            die('获取 pkey 失败');
        }
        //验证
        $re = openssl_verify($toSign_str, base64_decode($sig_dat), $pkid, OPENSSL_ALGO_SHA1);

        if(!$re){
            $this->errorMsg('error');
        }
    }

}