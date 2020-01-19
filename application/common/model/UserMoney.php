<?php

namespace app\common\model;

use think\Db;
use think\Model;

//余额变动
class UserMoney extends Model 
{
    /**
     * [reduceUserMoney 余额变动记录]
     * @param  [type] $money   [金额]
     * @param  [type] $type    [类型1团购 2课程 3购买会员 4万人砍 5返现 6提现 7会员奖励 8推荐人收入 9顾问奖励 10再次购买返现 11开会员返现 12提现失败]
     * @param  [type] $orderId [订单id]
     * @param  [type] $orderNo [订单号]
     */
    public function reduceUserMoney($money,$type,$orderId,$orderNo,$userId)
    {
        $user = $this->getUserInfo($userId);

        switch ($type) {
            case '1':
            case '2':
            case '3':
            case '4':
            case '6':
                $change_money = $user['balance']-$money;
                break;
            case '5':
            case '7':
            case '10':
            case '11':
            case '12':
                $change_money = $user['balance']+$money;
                break;
            case '8':
                if($user['isReferrer']!=1){
                    return 'isReferrer';
                }
                $change_money = $user['balance']+$money;
                break;
            case '9':
                if($user['isAdviser']!=1){
                    return 'isAdviser';
                }
                $change_money = $user['balance']+$money;
                break;
            default:
                return false;
                break;
        }
        if($change_money<0){
            return 'payNoMoney';
        }
        // echo $change_money;exit;
        $arr = [
            'balance' =>$change_money,
            'id'=>$user['id']
        ];
        Db::startTrans();
        try{
            db('user')->update($arr);
            $arr = [
                'userId'=>$user['id'],
                'nowMoney'=>$user['balance'],
                'changeMoney'=>$change_money,
                'money'=>$money,
                'createTime'=>time(),
                'type'=>$type,
                'orderId'=>$orderId,
                'orderNo'=>$orderNo,
            ];
            db('record_money')->insert($arr);
            // 提交事务
            Db::commit();
        }catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
        return true;
    }

    /**
     * [getSelf 获取用户信息]
     * @return [array] [用户信息]
     */
    public function getUserInfo($id)
    {
        $data = db('user')
        ->field('id,balance,isAdviser,isReferrer')
        ->where('id',$id)
        ->find();
        return $data;
    }

}