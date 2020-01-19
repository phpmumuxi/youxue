<?php

/**
 * 外部调用回调模型
 */
namespace app\api\model;

use think\Model;
use app\common\libs\Bankpay;



class BankAll extends Model
{

    public function getOnlineKey($type)
    {
        $re =db('online_key')
        ->where('type',$type)
        ->order('id desc')
        ->find();

        if($re && $re['create_time']>(time() - 3600*24)){
            return $re['message'];
        }

        $a = new Bankpay();
        $arr['message'] = $a->selectNetPay();
        $arr['create_time'] = time();
        $arr['type'] = $type;

        db('online_key')->insert($arr);
        return $arr['message'];
    }

}