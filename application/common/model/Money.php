<?php
/**
 * Created by PhpStorm.
 * User: Liu
 * Date: 2017-07-06
 * Time: 12:53
 * Date: 2017-08-30 作废
 */

namespace app\common\model;

use think\Model;

class Money extends Model
{
    /*
     *  金额变动记录
     *  uid:            用户id
     *  now_money:      变化前金额
     *  change_money:   变化后金额
     *  money:          变化的钱
     *  type:           类型：1团购 2课程 3会员 4商品 5充值 6提现 7豆豆购买 8豆豆销售 9返现 10会员奖励 11老师收入 12顾问奖励 13再次购买返现 14开会员返现 15体育玩乐券 16续费返现
     *  type_id:        类型对应点id
     *  return  0/1、新增记录id
    */
    public function insertRecord($uid, $now_money, $change_money, $money, $type, $type_id, $insertId = 0)
    {
        $data = ['user_id' => $uid, 'now_money' => $now_money, 'change_money' => $change_money, 'money' => $money, 'create_time' => time(), 'type' => $type, 'type_id' => $type_id];
        $ret = db('record_money')->insert($data);
        if ($insertId) {
            $ret = db('record_money')->getLastInsID();
        }
        return $ret;
    }

    /*
     *  获取用户余额
     *  uid:    用户id
     *  return  user's balance
     */
    public function userBalance($uid)
    {
        $userBalance = db('user')->where(['id' => $uid])->value('balance');
        return $userBalance;
    }

    /*
     *  更新用户余额
     *  uid:    用户余额
     *  money:  (正负)金额
     *  return  0/1
     */
    public function updateUserBalance($uid, $money)
    {
        $ret = db('user')->where(['id' => $uid])->setInc('balance',$money);
        return $ret;
    }
}