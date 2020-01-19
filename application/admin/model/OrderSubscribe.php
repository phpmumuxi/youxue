<?php
/**
 * User: LiuTong
 * Date: 2017/11/17
 * Time: 13:57
 */

namespace app\admin\model;


use think\Db;
use think\Exception;
use think\Model;

class OrderSubscribe extends Model
{
    /**
     * Date: 2017-11-17
     * 线下订单列表
     * @return mixed
     */
    public function getOrderSubscribeList()
    {
        $where['os.isDelete'] = 0;
        $data = db('order_subscribe os')
            ->field('os.*,u.name as uName,u.phone,an.name as adviserName,s.name as shopName,sc.name as schoolName,os.status')
            ->join('user u', 'u.id=os.userId', 'LEFT')
            ->join('adviser_name an', 'an.id=os.adviserId', 'LEFT')
            ->join('shop s', 's.id=os.shopId', 'LEFT')
            ->join('school sc', 'sc.id=os.schoolId', 'LEFT')
            ->where($where)
            ->paginate(10, false);
        return $data;
    }

    /**
     * Date: 2017-11-20
     * 检测是否已付尾款
     * @param $id
     * @return bool
     */
    public function checkOtherMoney($id)
    {
        $ret = db('order_subscribe')->where(['pid' => $id, 'isDelete' => 0, 'orderType' => 2])->find();
        return $ret ? true : false;
    }

    /**
     * Date: 2017-11-20
     * 退款确认
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function payBackOrder($id, $adminId)
    {
        Db::startTrans();
        try {
            db('order_subscribe')->where(['id' => $id])->update(['status' => 2]);
            operateLog('线下订单退款(订金)', 'order_subscribe', $id, $adminId);
        } catch (Exception $e) {
            die($e->getMessage());
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }
}