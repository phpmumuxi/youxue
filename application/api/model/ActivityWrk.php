<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-09-23
 */

namespace app\api\model;

use think\Db;
use think\Model;
use app\common\model\UserMoney as UserMoney;
use app\api\model\Back;

class ActivityWrk extends Model
{
    //1.活动列表
    public function activityListData($arr)
    {
        $data = db('activity_wrk')
            ->field('id as wrkId,name,startTime,endTime,listImg,status,surplus,money')
            ->where(['status'=>['in',[0,1,2]]])
            ->order('status,startTime desc')
            ->page($arr['page'],$arr['pagesize'])
            ->select();
        return $data;
    }

    //2.活动详情
    public function activityInfoData($id)
    {
        $data = db('activity_wrk')
            ->field('id as wrkId,name,startTime,endTime,topImg,status,surplus,classIds,money')
            ->where('id',$id)
            ->find();
        if(!$data)return false;
        if($data['status']===1){
            if($data['startTime']<=time()){
                db('activity_wrk')->update(['id'=>$id,'status'=>0]);
                $data['status'] = 0;
            }
        }
        if($data['status']===0){
            if($data['endTime']<=time()){
                db('activity_wrk')->update(['id'=>$id,'status'=>2]);
                $data['status'] = 2;
            }
        }
        $data['classInfo'] = $this->classInfo($data['classIds']);
        return $data;
    }

    //3.开抢
    public function activityOrderData($arr)
    {
        $data = db('activity_wrk')
            ->field('id as wrkId,status,classIds,money,price,surplus,name')
            ->where('id',$arr['wrkId'])
            ->find();
        if(!$data)return false;
        if($data['status'])return false;
        if($data['surplus']<=0)return false;
        $data['getMoney'] = $data['price']-$data['money'];
        unset($data['price']);
        $data['classInfo'] = $this->classInfo($data['classIds']);
        return $data;
    }

    //4.确认订单
    public function getActivityOrderData($arr)
    {
        $data = db('activity_wrk')
            ->field('id as wrkId,status,classIds,money,price,listImg,name')
            ->where('id',$arr['wrkId'])
            ->find();
        if(!$data)return false;
        if($data['status'])return false;
        $wrkOrder = [
            'orderNo'=>orderId('W'),
            'wrkId'=>$data['wrkId'],
            'classIds'=>$data['classIds'],
            'name'=>$data['name'],
            'userId'=>$arr['userId'],
            'price'=>$data['price'],
            'money'=>$data['money'],
            'listImg'=>$data['listImg'],
            'status'=>0,
            'payType'=>0,
            // 'payDate'=>0,
            'isDelete'=>0,
            'payMoney'=>0,
            'createTime'=>time(),
        ];
        // print_r($wrkOrder);exit;
        // $id=1;
        $id = db('activity_wrk_order')->insertGetId($wrkOrder);
        if(!$id)return false;
        $class = $this->wrkClass($data['classIds']);
        $schools = array_column($class,'schoolId');
        $schoolId = implode(',', $schools);
        $userInfo = $this->userInfo($arr['userId']);
        $userAdviser=$this->userAdviser($arr['userId'],$schoolId);
        $array = [];
        foreach ($class as $k => $v) {
            $array[$k]=[
                "userId"=>$arr['userId'],
                'wrkOrderId'=>$id,
                'wrkClassId'=>$v['id'],
                'name'=>$v['name'],
                'price'=>$v['price'],
                'money'=>$v['money'],
                'isSign'=>0,
                'brandId'=>$v['brandId'],
                'shopId'=>$v['shopId'],
                'schoolId'=>$v['schoolId'],
                'shopMoney'=>$v['shopMoney'],
                'referrerId'=>$userInfo['referrerId'],
                'referrerMoney'=>$v['referrerMoney'],
                'adviserMoney'=>$v['adviserMoney'],
                'starNum'=>$v['starNum'],
                'shareId'=>$userInfo['shareId'],
                'isDispose'=>0,
                'createTime'=>time(),
                'adviserId'=>0
            ];
            if($userAdviser){
               foreach ($userAdviser as $j => $val) {
                    if($v['schoolId']==$userAdviser[$j]['schoolId']){
                        $array[$k]['adviserId']=$userAdviser[$j]['adviserId'];
                    }
                }
            }
        }
        db('activity_wrk_info')->insertAll($array);
        return ['orderNo'=>$wrkOrder['orderNo'],'money'=>$data['money']];

    }

    //确认支付
    public function payActivityOrderData($arr)
    {
        $data = $this->wrkOrderInfo($arr);
        // print_r($data);die;
        if (!$data) return false;

        if (!($data['status'] == 0 || $data['status'] == 5)) {
            return false;
        }
        $money = $data['money'] - $data['payMoney'];
        if ($money > 100) {
            if ($arr['money'] < 100 || $arr['money'] > $money) {
                return false;
            }
        } else {
            if ($arr['money'] < $money || $arr['money'] > $money) {
                return false;
            }
        }
        // print_r($data);die;
        switch ($arr['payType']) {
            case 1://余额
                $res = $this->wrkBalancepay($arr, $data);
                break;
            case 2://支付宝
                $res = $this->wrkAlipay($arr, $data);
                break;
            case 3://银行卡
                $res = $this->wrkBankpay($arr, $data);
                break;
            case 4://微信
                $res = $this->wrkWerixinpay($arr, $data);
                break;
            default:
                $res = 0;
                break;
        }
        return $res;
    }

    //活动订单信息
    private function wrkOrderInfo($arr)
    {
        $data = db('activity_wrk_order')
            ->field('id,orderNo,wrkId,classIds,name,userId,price,money,status,payMoney,payType')
            ->where('orderNo="'.$arr['orderNo'].'" and userId='.$arr['userId'])
            ->find();
        return $data;
    }
     //余额
    private function wrkBalancepay($arr, $data)
    {
        $m = new UserMoney();
        $a = $m->reduceUserMoney($arr['money'], 4, $data['id'], $data['orderNo'], $data['userId']);
        if (!($a === true)) return false;
        $data1 = [
            'orderWrkId' => $data['id'],
            'orderNo' => orderId('WA'),
            'payType' => 1,
            'userId' => $data['userId'],
            'createTime' => time(),
            'money' => $arr['money'],
            'name' => $data['name'],
            'status' => 1,
            'isDelete' => 0,
            'payTime' => time()
        ];
        $res = $this->payWrkSuccessBalance($data, $data1, $arr);
        if (!$res) return false;
        $re = $this->payWrkSuccessDate(['orderNo' => $data['orderNo'], 'userId' => $data['userId']]);
        $re['payType'] = 1;
        return $re;
    }

     //支付宝
    private function wrkAlipay($arr, $data)
    {
        $data['money'] = $arr['money'];
        $re = $this->payWrkOrder($data);
        if (!$re) return false;
        $data = [
            'name' => $data['name'],
            'money' => $data['money'],
            'orderNo' => $re['orderNo']
        ];
        $a = new Back();
        $res = $a->alipayData($data);
        return $res;
    }

    //银行卡
    private function wrkBankpay($arr, $data)
    {
        $data['money'] = $arr['money'];
        $re = $this->payWrkOrder($data);
        if (!$re) return false;
        $data = [
            'name' => $data['name'],
            'money' => $data['money'],
            'order_no' => $re['orderNo']
        ];
        $a = new Back();
        $res = $a->bankData($data, $arr['userId']);
        return $res;

    }

    //微信
    private function wrkWerixinpay($arr, $data)
    {
        $data['money'] = $arr['money'];
        $re = $this->payWrkOrder($data);
        if (!$re) return false;
        $data = [
            'name' => $data['name'],
            'money' => $data['money'],
            'order_no' => $re['orderNo']
        ];
        $a = new Back();
        $res = $a->weixinData($data);
        return $res;
    }

    //第三方分笔支付生成订单
    private function payWrkOrder($data)
    {
        $arr = [
            'orderWrkId' => $data['id'],
            'orderNo' => orderId('WA'),
            'userId' => $data['userId'],
            'createTime' => time(),
            'money' => $data['money'],
            'name' => $data['name'],
            'status' => 0,
            'isDelete' => 0
        ];
        $re = db('activity_wrk_pay')->insert($arr);
        return $re ? ['orderNo' => $arr['orderNo']] : false;
    }

    //pos机扫码
    public function wrkQRCodePayData($arr)
    {
        $res = $this->wrkOrderInfo($arr);
        if (!$res || !($res['status']==0||$res['status']==5)) return false;
        $data = [];
        $random = '';
        for ($i = 1; $i <= 6; $i++) {
            $random .= chr(rand(97, 122));
        }
        $data['order_no'] = $arr['orderNo'];
        $data['money'] = $res['money']-$res['payMoney'];
        $data['random'] = $random;
        $data['account'] = '125907775810801';
        $encryption = "asf12uq17ad!!3s8!aa;a;kj%d#"; //加密码
        $data['sign'] = md5($data['order_no'] . $data['random'] . $encryption);
        return $data;
    }

    //余额分笔支付
    private function payWrkSuccessBalance($data, $data1)
    {
        $money = $data['payMoney'] + $data1['money'];
        $array = [
            'payMoney' => $money,
            'id' => $data['id'],
        ];
        if (!$data['payType']) {
            $array['payType'] = 1;
        }
        if (!$data['status']) {
            $array['status'] = 5;
        }
        $arr = [];
        if ($money == $data['money']) {
            $wrk = db('activity_wrk')
            ->field('id,surplus,sellNum')
            ->where('id',$data['wrkId'])
            ->find();
            $arr['id'] = $wrk['id'];
            $arr['surplus'] = $wrk['surplus']-1;
            $arr['sellNum'] = $wrk['sellNum']+1;
            $array['status'] = 1;
            $array['payDate'] = date('Y-m-d H:i:s');
        }else {
            $array['status'] = $data['status'] ? $data['status'] : 5;
        }
        $array['payType'] = $data['payType'] ? $data['payType'] : 1;

        Db::startTrans();
        try {
            db('activity_wrk_pay')->insert($data1);
            db('activity_wrk_order')->update($array);
            if($arr){
                db('activity_wrk')->update($arr);
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // echo $e->getMessage();exit;
            // 回滚事务
            Db::rollback();
            return false;
        }
        return true;
    }

    //第三方支付成功
    public function payWrkOrderSuccess($arr)
    {
        $data = db('activity_wrk_pay')->where('orderNo="' . $arr['orderNo'] . '" and status=0 and isDelete=0')->find();
        if (!$data) return false;
        $res = db('activity_wrk_order')->field('id,orderNo,userId,money,status,payType,payMoney,wrkId')->where('id=' . $data['orderWrkId'] . ' and isDelete=0')->find();

        $arr['id'] = $data['id'];
        $arr['status'] = 1;
        $arr['payTime'] = time();

        $money = $data['money'] + $res['payMoney'];
        $array = [
            'id' => $res['id'],
            'payMoney' => $money
        ];
        // echo $money;exit;
        $arr1 = [];
        if ($money == $res['money']) {
            $wrk = db('activity_wrk')
            ->field('id,surplus,sellNum')
            ->where('id',$res['wrkId'])
            ->find();
            $arr1['id'] = $wrk['id'];
            $arr1['surplus'] = $wrk['surplus']-1;
            $arr1['sellNum'] = $wrk['sellNum']+1;
            $array['status'] = 1;
            $array['payDate'] = date('Y-m-d H:i:s');
        }else {
            $array['status'] = $res['status'] ? $res['status'] : 5;
        }
        $array['payType'] = $res['payType'] ? $res['payType'] : 1;

        // print_r($arr);die;
        Db::startTrans();
        try {
            db('activity_wrk_pay')->update($arr);
            db('activity_wrk_order')->update($array);
            if($arr1){
                db('activity_wrk')->update($arr1);
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // echo $e->getMessage();exit;
            // 回滚事务
            Db::rollback();
            return false;
        }
        return true;

    }

    //支付成功返回数据
    public function payWrkSuccessDate($arr)
    {
        $data = db('activity_wrk_order')
                ->field('orderNo,name,price,money,payMoney,classIds')
                ->where('orderNo="'.$arr['orderNo'].'" and userId='.$arr['userId'])
                ->find();
        if(!$data)return $data;
        $data['getMoney'] = $data['price']-$data['money'];
        unset($data['price']);
        $data['classInfo'] = $this->classInfo($data['classIds']);
        return $data;
    }

    //课程详情
    private function classInfo($ids)
    {
        $data = db('activity_wrk_class w')
                    ->field('w.name,w.price,b.name as brandName,s.address,s.phone,w.id as classId,w.schoolId,longitude,latitude')
                    ->join('brand b','b.id=w.brandId','left')
                    ->join('school s','s.id=w.schoolId','left')
                    ->where(['w.id'=>['in',$ids]])
                    ->select();
        return $data;
    }
    //课程信息
    private function wrkClass($ids)
    {
        $data = db('activity_wrk_class')
            ->field('id,name,price,money,brandId,shopId,schoolId,shopMoney,adviserMoney,adviserMoney,referrerMoney,starNum')
            ->where(['id'=>['in',$ids]])
            ->select();
        return $data;
    }
    //获取用户信息
    private function userInfo($uid)
    {
        $data = db('user')->field('referrerId,shareId')->where('id',$uid)->find();
        return $data;
    }

    //获取用户与校区关系的顾问id
    private function userAdviser($uid,$schools)
    {
        $data = db('adviser_user_school')->field('schoolId,adviserId')->where([
                    'userId'=>$uid,
                    'schoolId'=>['in',$schools],
                    'isDelete'=>0
                ])->select();
        return $data;
    }

    //删除子订单
    public function delWrkOrderDate($arr)
    {
        $data = db('activity_wrk_order')->where('orderNo="' . $arr['orderNo'] . '" and userId=' . $arr['userId'] . ' and isDelete=0')->value('id');
        if (!$data) return false;
        $a = db('activity_wrk_pay')->where('orderWrkId=' . $data . ' and status=0 and isDelete=0')->update(['isDelete'=>1]);
        return $a ? true : false;
    }

    //删除订单
    public function cancelWrkOrderDate($arr)
    {
        $data = db('activity_wrk_order')->field('id,status')->where(['id' => $arr['orderWrkId'], 'userId' => $arr['userId'], 'isDelete' => 0])->find();
        if (!$data) return false;
        if ($data['status']) return false;
        Db::startTrans();
        try{
            db('activity_wrk_order')->update(['isDelete' => 1, 'id' => $data['id']]);
            db('activity_wrk_pay')->where('orderWrkId='.$data['id'])->delete();
            // 提交事务
            Db::commit();
        }catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
        return true;
    }

    //订单列表
    public function wrkOrderInfoData($arr)
    {
        $where = '';
        switch ($arr['type']){
            case 1:
                $where['status'] = 0;
                break;
            case 2:
                $where['status'] = 5;
                break;
            case 3:
                $where['status'] = ['in', [1, 4]];
                break;
            default:
                return false;
                break;
        }
        $where['userId'] = $arr['userId'];
        $where['isDelete'] = 0;
        $data = db('activity_wrk_order ')
            ->field('id as orderWrkId,name,money,payMoney,listImg')
            ->where($where)
            ->page($arr['page'], $arr['pagesize'])
            ->order('id desc')
            ->select();
        return $data;
    }

    //订单详情
    public function wrkOrderOneInfoDate($arr)
    {
        $data = db('activity_wrk_order')
            ->field('id as orderWrkId,orderNo,name,status,classIds,money,price,payMoney')
            ->where('id',$arr['orderWrkId'])
            ->where('userId',$arr['userId'])
            ->find();
        if (!$data) return $data;
        $data['getMoney'] = $data['price']-$data['money'];
        unset($data['price']);
        $data['classInfo'] = $this->classInfo($data['classIds']);
        if ($arr['type'] == 2 || $arr['type'] == 3) {
            $data['payInfo'] = db('activity_wrk_pay')->field('payType,paytime,money')->where(['orderWrkId' => $arr['orderWrkId'], 'userId' => $arr['userId'], 'status' => 1])->select();
        }
        return $data;
    }
}