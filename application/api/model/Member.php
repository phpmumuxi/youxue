<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-09-20
 */

namespace app\api\model;

use think\Db;
use think\Model;
use app\common\model\UserMoney as UserMoney;
use app\api\model\Back;

//会员
class Member extends Model
{

    public function memberInfoData()
    {
        $data = db('user_member')
            ->field('id,title,img,money,month,level,mMoney,prerogative')
            ->where('isUse=1')
            ->select();
        if(!$data)return $data;
        $re = $this->memberPreInfoData();
        foreach ($data as $k => $v) {
            $prerogative=explode(',', $v['prerogative']);
            foreach ($re as $j => $val) {
                foreach ($prerogative as $i => $value) {
                    if($value==$val['id']){
                        $data[$k]['pre'][]=[
                            'name'=>$val['name'],
                            'vipIco' => $val['vipIco']
                        ];
                    }
                }
            }

            unset($data[$k]['prerogative']);
        }
        return $data;

    }

    public function memberPreInfoData()
    {
        $data = db('user_member_ico')->field('id,name,vipIco,text')->where('isDelete=0')->select();
        return $data;
    }

    //确认订单
    public function memberOrderData($arr)
    {
        $data = db('user_member')->field('id,title,money,month,level,mMoney')->where(['level'=>$arr['level'],'isUse'=>1])->find();
        if(!$data)return false;
        if($arr['isCmbc']){
            $r = $this->userMember($arr['userId']);
            if(!$r['isCmbc'])$arr['isCmbc']=0;
        }
        $array = [
            'orderNo'=>orderId('M'),
            'memberId'=>$data['id'],
            'money'=>$arr['isCmbc']?$data['mMoney']:$data['money'],
            'createTime' =>time(),
            'userId'=>$arr['userId'],
            'level'=>$arr['level'],
            'month'=>$data['month'],
            'status'=>0,
            'type'=>1,
            'name'=>$data['title']
        ];
        $a = db('order_member')->insertGetId($array);
        return $a?['orderMemberId'=>$a,'money'=>$array['money']]:false;

    }

    //购买会员
    public function buyMemberData($arr)
    {
        $data = $this->orderMember($arr);
        if(!$data||$data['status']!=0)return false;
        switch ($arr['payType']) {
            case 1://余额
                $res = $this->memberBalancepay($data);
                break;
            case 2://支付宝
                $res = $this->memberAlipay($data);
                break;
            case 3://银行卡
                $res = $this->memberBankpay($data);
                break;
            case 4://微信
                $res = $this->memberWerixinpay($data);
                break;
            default:
                $res = 0;
                break;
        }
        return $res;
    }
    //余额
   private function memberBalancepay($data)
   {
        $m = new UserMoney();
        $a = $m->reduceUserMoney($data['money'],3,$data['id'],$data['orderNo'],$data['userId']);
        if(!($a===true))return false;
        $arr = [
            'id' => $data['id'],
            'status' => 1,
            'payTime' => time(),
            'payType'=>1,
        ];
        db('order_member')->update($arr);
        $this->memberSuccess($data);
        return true;


   }
   //支付宝
   private function memberAlipay($data)
   {
        $arr = [
            'name' => $data['name'],
            'money' => $data['money'],
            'orderNo' => $data['orderNo']
        ];
        $a = new Back();
        $res = $a->alipayData($arr);
        return $res;
   }
   //银行卡
   private function memberBankpay($data)
   {
         $arr = [
            'name' => $data['name'],
            'money' => $data['money'],
            'order_no' => $data['orderNo']
        ];
        $a = new Back();
        $res = $a->bankData($arr,$data['userId']);
        return $res;

   }
   //微信
   private function memberWerixinpay($data)
   {
         $arr = [
            'name' => $data['name'],
            'money' => $data['money'],
            'order_no' => $data['orderNo']
        ];
        $a = new Back();
        $res = $a->weixinData($arr);
        return $res;
   }

   //第三方支付成功
   public function payBackSuccess($arr)
   {
        $data = db('order_member')->where('orderNo',$arr['orderNo'])->find();
        if(!$data||$data['status']!=0)return false;
        $a = db('order_member')->update([
                'id'=>$data['id'],
                'status'=>1,
                'payType'=>$arr['payType'],
                'payRecord'=>$arr['payRecord'],
                'payTime'=>time()
            ]);
        $this->memberSuccess($data);
        return true;
   }

   private function memberSuccess($arr)
   {
        $re = $this-> userMember($arr['userId']);
        $data['id']=$arr['userId'];
        $data['memberLevel']=$arr['level'];
        $data['memberEndTime']=time()+$arr['month']*2635200;
        if($re['level']){
            if($re['level']>$arr['level']){
                db('user_member_use')->insert([
                        'userId'=>$arr['userId'],
                        'level'=>$arr['level'],
                        'month'=>$arr['month'],
                        'createTime'=>time(),
                        'isUse'=>0
                    ]);
                return true;
            }else{
                $data['memberEndTime']=$re['memberEndTime']+$arr['month']*2635200;

            }
        }
        db('user')->update($data);
        return true;
   }

    private function orderMember($arr)
    {
        $data = db('order_member')->field('id,name,orderNo,memberId,money,userId,level,month,status')->where(['id'=>$arr['orderMemberId'],'userId'=>$arr['userId']])->find();
        return $data;
    }

    private function userMember($uid)
    {
        $data = db('user')->field('isCmbc,memberLevel,memberEndTime')->where('id',$uid)->find();
        $data['level'] = $data['memberLevel'];
        if($data['memberLevel']&&$data['memberEndTime']<time()){
             $data['level'] = 0;
        }
        return $data;
    }

    private function useMember($uid)
    {
        $data = db('user_member_use')->field('level,month')->where(['userId'=>$uid,'isUse'=>0])->find();
        return $data;
    }

    //成功之后返回数据
    public function buyMemberSuccessData($arr)
    {
        $data = db('order_member')->where('id',$arr['orderMemberId'])->find();
        if(!$data||$data['status']!=1)return null;
        $data['userInfo']=$this->userMember($data['userId']);
        return [
            'name'=>$data['name'],
            'level'=>$data['level'],
            'month'=>$data['month'],
            'payType'=>$data['payType'],
            'money'=>$data['money'],
            'userInfo'=>$data['userInfo']
        ];
    }


}