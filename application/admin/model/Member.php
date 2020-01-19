<?php
/**
 * User: LiuTong
 * Date: 2017-08-23
 * Time: 13:35
 */

namespace app\admin\model;

use think\Model;

class Member extends Model
{
    /**
     * 会员列表
     * Date: 2107-08-23
     * @param string $phone
     * @return \think\Paginator
     */
    public function getMemberList($phone = '')
    {
        $where = $phone ? ['phone' => $phone] : '';
        $data = db('user')
            ->field('id,name,phone,memberLevel,isAdviser,isReferrer,balance,doudou,createTime')
            ->order('balance DESC')
            ->where($where)
            ->paginate(10, false, ['query' => ['phone' => $phone]]);
        return $data;
    }

    /**
     * 会员详细信息
     * Date: 2017-08-23
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     */
    public function getMemberInfo($id)
    {
        $data = db('user')->where(['id' => $id])->find();
        return $data;
    }

    /**
     * 会员收入支出记录
     * Date: 2017-08-24
     * @param $uid
     * @param string $orderNo
     * @return \think\Paginator
     */
    public function getMemberMoneyRecord($uid, $orderNo = '')
    {
        $where['userId'] = $uid;
        if ($orderNo) {
            $where['orderNo'] = $orderNo;
        }
        $data = db('record_money')
            ->field('id,nowMoney,changeMoney,money,createTime,type,orderNo')
            ->where($where)
            ->order('createTime DESC')
            ->paginate(10, false, ['query' => ['orderNo' => $orderNo, 'id' => $uid]]);
        return $data;
    }

    /**
     * 会员课程订单记录
     * Date: 2017-08-28
     * @param $uid
     * @param string $orderNo
     * @return mixed
     */
    public function getMemberCourseOrder($uid, $orderNo = '')
    {
        $where['userId'] = $uid;
        $where['isDelete'] = 0;
        if ($orderNo) {
            $where['orderNo'] = $orderNo;
        }
        $data = db('order_class')
            ->field('id,orderNo,money,createTime,name,status,isSign')
            ->where($where)
            ->order('createTime DESC')
            ->paginate(10, false, ['query' => ['orderNo' => $orderNo, 'id' => $uid]]);
        return $data;
    }

    /**
     * 会员课程订单详细
     * Date: 2017-08-28
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     */
    public function getMemberCourseOrderDetail($id)
    {
        $data = db('order_class oc')
            ->field('oc.*,u.name as uName,u.phone,s.name as shopName,sc.name as schoolName')
            ->join('user u', 'u.id=oc.userId', 'LEFT')
            ->join('shop s', 's.id=oc.shopId', 'LEFT')
            ->join('school sc', 'sc.id=oc.schoolId', 'LEFT')
            ->where(['oc.id' => $id])
            ->find();
        $data['sp'] = $this->getMemberCourseOrderSeparatePaymentDetail($id);
        return $data;
    }

    /**
     * 订单分笔支付详情
     * Date: 2017-08-28
     * @param $id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getMemberCourseOrderSeparatePaymentDetail($id)
    {
        $data = db('order_class_pay')
            ->field('payType,payRecord,payTime,money,status')
            ->where(['orderClassId' => $id, 'isDelete' => 0])
            ->select();
        return $data;
    }

    /**
     * 团购订单列表
     * Date: 2017-08-29
     * @param $uid
     * @param $orderNo
     * @return mixed
     */
    public function getMemberGroupBuyCourseOrder($uid, $orderNo)
    {
        $where['ob.userId'] = $uid;
        if ($orderNo) {
            $where['ob.orderNo'] = $orderNo;
        }
        $data = db('order_buy ob')
            ->field('ob.id,ob.orderNo,ob.name,ob.money,ob.useTime,ob.status,ob.payTime,ob.term,ob.termDay,ob.isDispose')
            ->where($where)
            ->order('ob.createTime DESC')
            ->paginate(10, false, ['query' => ['orderNo' => $orderNo, 'id' => $uid]]);
        return $data;
    }

    /**
     * Vip免费体验课程
     * Date: 2017-08-31
     * @param $uid
     * @return mixed
     */
    public function getMemberVipFreeCourse($uid)
    {
        $where['uc.userId'] = $uid;
        $data = db('user_class uc')
            ->field('uc.id,b.name as brandName,s.name as shopName,uc.createTime,uc.status,uc.useTime,cs.name as courseName')
            ->join('brand b', 'b.id=uc.brandId', 'LEFT')
            ->join('shop s', 's.id=uc.shopId', 'LEFT')
            ->join('class_school cs', 'cs.id=uc.classSchoolId', 'LEFT')
            ->where($where)
            ->order('uc.createTime DESC')
            ->paginate(10, false, ['query' => ['id' => $uid]]);
        return $data;
    }

}