<?php
/**
 * User: LiuTong
 * Date: 2017-09-28
 * Time: 11:41
 */

namespace app\school\model;

use think\Db;
use think\Exception;
use think\Model;

class StarEvent extends Model
{
    /**
     * Date: 2017-09-28
     * 赠送课程
     * @param $schoolId
     * @return \think\Paginator
     */
    public function getStarOrderGive($schoolId)
    {
        $where['sfu.schoolId'] = $schoolId;
        $where['sfu.adviserId'] = ['exp', '>0'];
        $data = db('star_free_use sfu')
            ->field('sfu.id,sfu.userId,sfu.status,sfu.createTime,sfu.useTime,u.name as uName,u.phone,an.name as adviserName')
            ->join('user u', 'u.id=sfu.userId', 'LEFT')
            ->join('adviser_name an', 'an.id=sfu.adviserId', 'LEFT')
            ->where($where)
            ->paginate(10);
        return $data;
    }

    /**
     * Date: 2017-09-28
     * 星星灯券课程
     * @param $schoolId
     * @return mixed
     */
    public function getStarOrderTicket($schoolId)
    {
        $where['stu.schoolId'] = $schoolId;
        $where['stu.adviserId'] = ['>', '0'];
        $data = db('star_ticket_use stu')
            ->field('stu.id,stu.className,stu.classNum,stu.status,stu.useTime,stu.createTime,u.name as uName,u.phone,an.name as adviserName')
            ->join('user u', 'u.id=stu.userId', 'LEFT')
            ->join('adviser_name an', 'an.id=stu.adviserId', 'LEFT')
            ->where($where)
            ->paginate(10);
        return $data;
    }

    /**
     * Date: 2017-09-28
     * 星星兑换
     * @param $schoolId
     * @return mixed
     */
    public function getStarOrderExchange($schoolId)
    {
        $where['su.schoolId'] = $schoolId;
        $where['su.adviserId'] = ['>', '0'];
        $data = db('star_use su')
            ->field('su.id,su.starNum,su.className,su.classNum,su.status,su.useTime,su.createTime,u.name as uName,u.phone,an.name as adviserName')
            ->join('user u', 'u.id=su.userId', 'LEFT')
            ->join('adviser_name an', 'an.id=su.adviserId', 'LEFT')
            ->where($where)
            ->paginate(10);
        return $data;
    }

    /**
     * Date: 2017-09-28
     * 确认免费赠送券已使用
     * fix Date: 2017-11-01
     * fix Content: 增加 更新t_adviser_order 订单状态
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function makeGiveSure($id, $adminId)
    {
        $adviserOrderId = db('adviser_order')->where(['orderId' => $id, 'orderType' => 4])->value('id');
//        4星星灯新用户免费券5星星灯领取课6星星兑换课7万人砍
        Db::startTrans();
        try {
            db('star_free_use')->where(['id' => $id])->update(['status' => 1, 'useTime' => time()]);
            operateLog('确认免费赠送券已使用', 'star_free_use', $id, $adminId);
            if ($adviserOrderId) {
                db('adviser_order')->where(['orderId' => $id, 'orderType' => 4])->update(['status' => 1]);
                operateLog('更新顾问订单表状态 免费赠送券已使用', 'adviser_order', $adviserOrderId, $adminId);
            } else {
                operateLog('未找到星星灯赠送课程顾问管理订单 orderType = 4 star_free_use orderId = ' . $id, 'adviser_order', 0, $adminId);
            }
        } catch (Exception $e) {
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

    /**
     * Date: 2017-09-28
     * 确认星星灯赠送券已使用
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function makeTicketSure($id, $adminId)
    {
        $adviserOrderId = db('adviser_order')->where(['orderId' => $id, 'orderType' => 5])->value('id');
        Db::startTrans();
        try {
            db('star_ticket_use')->where(['id' => $id])->update(['status' => 1, 'useTime' => time()]);
            operateLog('确认星星灯兑换券已使用', 'star_ticket_use', $id, $adminId);
            if ($adviserOrderId) {
                db('adviser_order')->where(['orderId' => $id, 'orderType' => 5])->update(['status' => 1]);
                operateLog('更新顾问订单表状态 星星灯赠送券兑换课已使用', 'adviser_order', $adviserOrderId, $adminId);
            } else {
                operateLog('未找到星星灯赠送券兑换课程顾问管理订单 orderType = 5 star_ticket_use orderId = ' . $id, 'adviser_order', 0, $adminId);
            }
        } catch (Exception $e) {
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

    /**
     * Date: 2017-09-28
     * 确认星星兑换已上课
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function makeExchangeSure($id, $adminId)
    {
        $adviserOrderId = db('adviser_order')->where(['orderId' => $id, 'orderType' => 6])->value('id');
        Db::startTrans();
        try {
            db('star_use')->where(['id' => $id])->update(['status' => 1, 'useTime' => time()]);
            operateLog('确认星星兑换课程已上课', 'star_use', $id, $adminId);
            if ($adviserOrderId) {
                db('adviser_order')->where(['orderId' => $id, 'orderType' => 6])->update(['status' => 1]);
                operateLog('更新顾问订单表状态 星星兑换课已使用', 'adviser_order', $adviserOrderId, $adminId);
            } else {
                operateLog('未找到星星兑换课程顾问管理订单 orderType = 5 star_use orderId = ' . $id, 'adviser_order', 0, $adminId);
            }
        } catch (Exception $e) {
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

}