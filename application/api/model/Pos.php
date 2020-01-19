<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-09-23
 */

namespace app\api\model;

use think\Db;
use think\Model;

class Pos extends Model
{

    //扫码支付
    public function receiveData($arr)
    {
        $type = $this->orderNo($arr['order_no']);
        $data = 0;
        if($type == 1){
            $data = $this->classInfo($arr);
        }elseif($type==2){
            $data = $this->wrkClassInfo($arr);
        }else{
            return false;
        }
        return [
                'phone'=>$data['phone'],
                'info'=>['orderNo'=>$data['orderNo'],'type'=>$data['type']]
        ];
    }
    //判断订单类型
    private function orderNo($order)
    {
        $a = substr($order , 0 , 1);
        $type = 0;
        if($a=="C"){
            $type=1;//普通课程
        }elseif($a=='W'){
            $type=2;//万人砍
        }
        return $type;
    }
    //普通课程支付
    private function classInfo($arr)
    {
        $data = db("order_class")
                ->field('id,orderNo,money,name,status,userMoney,userId,classSchoolId,isAgain,payMoney')
                ->where(['orderNo'=>$arr['order_no'],'isDelete'=>0])
                ->find();
        // print_r($data);die;
        if(!$data||!($data['status']==0||$data['status']==5)){
            return false;
        }
        $array = [
            'orderClassId'=>$data['id'],
            'orderNo'=>$data['orderNo'],
            'payNo'=>$arr['pay_no'],
            'name'=>$data['name'],
            'money'=>$data['money']-$data['payMoney'],
            'userId'=>$data['userId'],
            'posId'=>$arr['pos_id'],
            'payRecord'=>$arr['pay_record'],
            'status'=>1,
            'createTime'=>time()
        ];
        $orderData = [
            'id'=>$data['id'],
            'status'=>1,
            'payDate'=>date('Y-m-d H:i:s'),
            'payType'=>2,
            'payMoney'=>$array['money']+$data['payMoney']
        ];
        $user = [];
        $userInfo = $this->userPhone($data['userId']);
        if(!$userInfo['isGive']){
            $user['isGive']=1;
            if(!$userInfo['memberLevel']){
                $user['memberLevel'] = 1;
                $num = $this->vipInfo(1);
                $user['memberEndTime'] = time()+$num*2635200;
                $orderData['level'] = 1;
                // $orderData['userMoney'] = $this->classRule($data['classSchoolId']);
                // $data['userMoney'] = $orderData['userMoney'];
            }
        }
        Db::startTrans();
        try{
            db('order_class_pos')->insert($array);
            db('order_class')->update($orderData);
            if($user){
                $user['id'] = $data['userId'];
                db('user')->update($user);
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            echo $e->getMessage();exit;
            // 回滚事务
            Db::rollback();
            return false;
        }
        // $m = new \app\common\model\UserMoney();
        // $m->reduceUserMoney($data['userMoney'],5,$data['id'],$data['orderNo'],$data['userId']);
        return['phone'=>$userInfo['phone'],'orderNo'=>$data['orderNo'],'type'=>1];
    }

    //万人砍课程支付
    private function wrkClassInfo($arr)
    {
        $data = db("activity_wrk_order")
                ->field('id,orderNo,money,name,status,userId,wrkId,payMoney')
                ->where(['orderNo'=>$arr['order_no'],'isDelete'=>0])
                ->find();
        if(!$data||!($res['status']==0||$res['status']==5)){
            return false;
        }
        $array = [
            'orderWrkId'=>$data['id'],
            'orderNo'=>$data['orderNo'],
            'payNo'=>$arr['pay_no'],
            'name'=>$data['name'],
            'money'=>$data['money']-$data['payMoney'],
            'userId'=>$data['userId'],
            'posId'=>$arr['pos_id'],
            'payRecord'=>$arr['pay_record'],
            'status'=>1,
            'createTime'=>time()
        ];
        $orderData = [
            'id'=>$data['id'],
            'status'=>1,
            'payDate'=>date('Y-m-d H:i:s'),
            'payType'=>2,
            'payMoney'=>$data['money']+$data['payMoney']
        ];
        $wrk = db('activity_wrk')
            ->field('id surplus,sellNum')
            ->where('id',$data['wrkId'])
            ->find();
        $wrkinfo['id'] = $wrk['id'];
        $wrkinfo['surplus'] = $wrk['surplus']-1;
        $wrkinfo['sellNum'] = $wrk['sellNum']+1;

        $user = [];
        $userInfo = $this->userPhone($data['userId']);
        Db::startTrans();
        try{
            db('activity_wrk_pos')->insert($array);
            db('activity_wrk_order')->update($orderData);
            db('activity_wrk')->update($wrkinfo);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // echo $e->getMessage();exit;
            // 回滚事务
            Db::rollback();
            return false;
        }
        return['phone'=>$userInfo['phone'],'orderNo'=>$data['orderNo'],'type'=>2];
    }

    //用户信息
    private function userPhone($uid)
    {
        $data = db('user')->field('phone,memberLevel,isGive')->where('id',$uid)->find();
        return $data;
    }

    //查询会员
    private function vipInfo($num)
    {
        $data = db('user_member')->where('isUse=1 and level='.$num)->value('month');
        return $data;
    }
    //返现规则
    private function classRule($classSchoolId)
    {
        $userMoney = db('class_rule')->where(['classSchoolId'=>$classSchoolId,'isDelete'=>0])->value('lZero');
        return $userMoney;
    }


    //订单绑定支付
    public function orderQueryData($str)
    {
        $arr = explode('|',$str);
        $array = json_decode(end($arr),true);

        if(!isset($array['resultInfos']))return false;
        $data = $array['resultInfos'][0];
        $re = db('order_class_postwo')->where([
                'extOrderId'=>$data['extOrderId'],
                'status'=>9
            ])->find();
        if(!$re)return false;
        $time = strtotime($data['orderTime']);
        $order_state = 0;
        if($data['orderStatus']==1&&$data['amount']==$re['money']){
            $order_state = 1;
            $res = $this->classOrder(['orderClassId'=>$re['orderClassId'],'userId'=>$re['userId']]);
            if(!$re)return false;

            $orde = [
                    'id'=>$re['orderClassId'],
                    'status'=>1,
                    'payDate'=>date('Y-m-d H:i:s'),
                    'payType'=>3,
                    'payMoney'=>$data['amount']+$res['payMoney']
                ];
            $user = [];
            $userInfo = $this->userPhone($re['userId']);
            if(!$userInfo['isGive']){
                $user['isGive']=1;
                if(!$userInfo['memberLevel']){
                    $user['memberLevel'] = 1;
                    $num = $this->vipInfo(1);
                    $user['memberEndTime'] = time()+$num*2635200;
                    $orde['level'] = 1;
                    $orde['userMoney'] = $this->classRule($res['classSchoolId']);
                    $res['userMoney'] =  $orde['userMoney'];
                }
            }

            Db::startTrans();
            try{
                db('order_class')->update($orde);
                if($user){
                    $user['id'] = $res['userId'];
                    db('user')->update($user);
                }
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                echo $e->getMessage();exit;
                // 回滚事务
                Db::rollback();
                return false;
            }
            // $m = new \app\common\model\UserMoney();
            // $m->reduceUserMoney($res['userMoney'],5,$res['id'],$res['orderNo'],$res['userId']);
        }
        if($data['orderStatus']!=1)$order_state = 3;
        if($data['orderStatus']==1&&$data['amount']!=$re['money'])$order_state = 2;
        db('order_class_postwo')->update([
                'id'=>$re['id'],
                'orderTime'=>$data['orderTime'],
                'flowNo'=>$data['flowNo'],
                'status'=>$data['orderStatus'],
                'acquirerId'=>$data['acquirerId'],
                'issuerId'=>$data['issuerId'],
                'payMoney'=>$data['amount'],
                'cardNo'=>$data['cardNo'],
                'referNo'=>$data['referNo'],
                'orderId'=>$re['orderId'],
                'orderState' => $order_state
            ]);
        return true;

    }


    public function classOrder($arr)
    {
        $re=db('order_class')
            ->field('id,orderNo,money,name,status,userMoney,userId,classSchoolId,payMoney')
            ->where([
                    'id'=>$arr['orderClassId'],
                    'userId'=>$arr['userId'],
                    'status'=>['in',[0,5]],
                    'isDelete'=>0
                ])->find();
        return $re;
    }
}