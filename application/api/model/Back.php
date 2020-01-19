<?php

/**
 * 外部调用回调模型
 */
namespace app\api\model;

use think\Model;
use app\common\libs\Alipay;
use app\common\libs\Weixin;
use app\common\libs\Bankpay;



class Back extends Model
{

    // 微信回调地址
    public $back_url = 'http://tcyx.jsrzc.top/api';


    // 1.支付宝
    public function alipayData($arr)
    {
        $arr['back_url'] = $this->back_url . '/back/alipayCallbackData';

        $re = $this->payAli($arr);

        return $re;
    }

    // 2.微信
    public function weixinData($arr)
    {
        $arr['back_url'] = $this->back_url . '/back/weixinCallbackData';

        $re = $this->payWeixin($arr);

        return $re;
    }

    // 3.银行卡
    public function bankData($arr,$uid)
    {
        $arr['back_url'] = $this->back_url . '/back/bankCallbackData';

        $re = $this->payBank($arr,$uid);

        return $re;
    }


    public function payWeixin($arr)
    {
        $pay = new Weixin();
        return $pay->sign($arr);
    }

    public function payAli($arr)
    {
        $pay = new Alipay();
        return $pay->sdkExecute($arr);
    }

    public function payBank($arr,$uid)
    {
        $user = db('user')->field('agr_no,id,phone')->where("id",$uid)->find();
        if(!$user){
            return false;
        }
        if(!$user['agr_no']){
            $user['agr_no'] = $user['phone'].rand(100000,999999);
            db('user')->update(['id'=>$user['id'],'agr_no'=>$user['agr_no']]);
        }
        $arr['agr_no'] = $user['agr_no'];

        $pay = new Bankpay();
        return $pay->sign($arr);
    }
}