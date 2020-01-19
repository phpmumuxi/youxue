<?php
/**
 * User: LiuTong
 * Date: 2017-09-22
 * Time: 16:53
 */

namespace app\school\model;

use app\common\controller\Jpush;
use think\Db;
use think\Exception;
use think\Model;

class OrderHandle extends Model
{

    /**
     * Date: 2017-09-22
     * 订单处理
     * @param $schoolId
     * @param int $type
     * @return array|mixed
     */
    public function getOrderHandleList($schoolId, $type = 1)
    {
        $data = [];
        switch ($type) {
            case 1:
                $data = $this->A($schoolId);
                break;
            case 2:
                $data = $this->B($schoolId);
                break;
            case 3:
                $data = $this->C($schoolId);
                break;
            case 4:
                $data = $this->D($schoolId);
                break;
            default:
                $data = $this->A($schoolId);
        }
        return $data;
    }

    /**
     * Date: 2017-09-22
     * 课程
     * @param $schoolId
     * @return mixed
     */
    private function A($schoolId)
    {
        $where['o.isDispose'] = 0;
        $where['o.schoolId'] = $schoolId;
        $data = db('order_class o')
            ->field('o.id,o.orderNo,o.name,o.status,o.adviserId,o.createTime,o.userId,u.name as uName,u.phone,an.name as adviserName,1 as type')
            ->join('user u', 'u.id=o.userId', 'LEFT')
            ->join('adviser_name an', 'an.id=o.adviserId', 'LEFT')
            ->where($where)
            ->select();
        return $data;
    }

    /**
     * Date: 2017-09-22
     * 团购
     * @param $schoolId
     * @return mixed
     */
    private function B($schoolId)
    {
        $where['ob.schoolId'] = $schoolId;
        $where['ob.isDispose'] = 0;
        $where['ob.status'] = 1;
        $data = db('order_buy ob')
            ->field('ob.id,ob.orderNo,ob.name,ob.status,ob.adviserId,ob.createTime,ob.userId,u.name as uName,an.name as adviserName,2 as type')
            ->join('user u', 'u.id=ob.userId', 'LEFT')
            ->join('adviser_name an', 'an.id=ob.adviserId', 'LEFT')
            ->where($where)
            ->select();
        return $data;
    }

    /**
     * Date: 2017-09-22
     * 体验课
     * @param $schoolId
     * @return mixed
     */
    private function C($schoolId)
    {
        $where['uc.schoolId'] = $schoolId;
        $where['uc.status'] = 0;
        $data = db('user_class uc')
            ->field('uc.id,0 as orderNo,cs.name,uc.status,uc.adviserId,uc.createTime,uc.userId,u.name as uName,an.name as adviserName,3 as type')
            ->join('class_school cs', 'cs.id=uc.schoolId', 'LEFT')
            ->join('user u', 'u.id=uc.userId', 'LEFT')
            ->join('adviser_name an', 'an.id=uc.adviserId', 'LEFT')
            ->where($where)
            ->select();
        return $data;
    }

    /**
     * Date: 2017-09-22
     * 星星点灯兑换课程
     * @param $schoolId
     * @return mixed
     */
    private function D($schoolId)
    {
        $where['stu.schoolId'] = $schoolId;
        $where['stu.status'] = 0;
        $where['stu.adviserId'] = 0;
        $data = db('star_ticket_use stu')
            ->field('stu.id,0 as orderNo,stu.className as name,stu.status,stu.adviserId,stu.createTime,stu.userId,u.name as uName,an.name as adviserName,4 as type')
            ->join('user u', 'u.id=stu.userId', 'LEFT')
            ->join('adviser_name an', 'an.id=stu.adviserId', 'LEFT')
            ->where($where)
            ->select();
        return $data;
    }

    /**
     * Date: 2017-09-22
     * 未处理、未分配顾问数
     * fix Date: 2017-10-27
     * fix Content: 增加万人砍订单
     * @param $schoolId
     * @return int|string
     */
    public function countAZ($schoolId)
    {
        $numA = db('order_class')->where(['schoolId' => $schoolId, 'isDispose' => 0, 'status' => 1])->count('id');
        $numB = db('order_buy')->where(['schoolId' => $schoolId, 'isDispose' => 0, 'status' => 1])->count('id');
        $numC = db('user_class')->where(['schoolId' => $schoolId, 'isDispose' => 0])->count('id');
        $numD = db('star_ticket_use')->where(['schoolId' => $schoolId, 'isDispose' => 0])->count('id');
        $numE = db('star_free_use')->where(['schoolId' => $schoolId, 'isDispose' => 0])->count('id');
        $numF = db('star_use')->where(['schoolId' => $schoolId, 'isDispose' => 0])->count('id');
        // 万人砍活动课程（活动包子课程）
        $numG = db('activity_wrk_info awi')
            ->join('activity_wrk_order awo', 'awo.id=awi.wrkOrderId', 'LEFT')
            ->where(['awi.schoolId' => $schoolId, 'awi.isDispose' => 0, 'awo.status' => 1])
            ->count('awi.id');
        return [
            'all' => ($numA + $numB + $numC + $numD + $numE + $numF + $numG),
            'numA' => $numA,
            'numB' => $numB,
            'numC' => $numC,
            'numD' => $numD,
            'numE' => $numE,
            'numF' => $numF,
            'numG' => $numG
        ];
    }

    /**
     * Date: 2017-09-25
     * 校区顾问列表
     * @param $schoolId
     * @return mixed
     */
    public function getSchoolAdviserList($schoolId)
    {
        $where['schoolId'] = $schoolId;
        $where['isDelete'] = 0;
        $data = db('adviser_name')
            ->field('id,name,userId,phone')
            ->where($where)
            ->select();
        return $data;
    }

    /**
     * Date: 2017-09-25
     * 分配顾问
     * fix Date: 2017-10-27
     * fix Content: 增加万人砍分配顾问
     * @param $type
     * @param $id
     * @param $adviserId
     * @param $schoolId
     * @param $adminId
     * @return bool
     */
    public function assignTypeAdviser($type, $id, $adviserId, $schoolId, $adminId)
    {
        // 指定订单类型分配顾问
        switch ((int)$type) {
            case 1:
                $table = 'order_class';
                $field = 'id,userId,name,money,isOldCustom';
                $orderType = 3;
                break;
            case 2:
                $table = 'order_buy';
                $field = 'id,userId,"体验课" as name,money,2 as isOldCustom';
                $orderType = 2;
                break;
            case 3:
                $table = 'star_free_use';
                $field = 'id,userId,"体验课" as name,0 as money,2 as isOldCustom';
                $orderType = 4;
                break;
            case 4:
                $table = 'star_ticket_use';
                $field = 'id,userId,className as name,0 as money,2 as isOldCustom';
                $orderType = 5;
                break;
            case 5:
                $table = 'star_use';
                $field = 'id,userId,className as name,0 as money,2 as isOldCustom';
                $orderType = 6;
                break;
            case 6:
                $table = 'user_class';
                $field = 'id,userId,"体验课" as name,0 as money,2 as isOldCustom';
                $orderType = 1;
                break;
            case 7:
                $table = 'activity_wrk_info';
                $field = 'id,userId,name,price as money,2 as isOldCustom';
                $orderType = 7;
                break;
            default:
                return false;
                break;
        }

        //  订单状态 0未体验1已体验2未签约3已签约（0,1体验课用/3,4优选课程用）
        //  订单类别 1vip体验课2团购3优选课程4星星灯新用户免费券5星星灯领取课6星星兑换课7万人砍
        $info = db($table)->field($field)->where(['id' => $id])->find();
        if (!$info) {
            return false;
        }
        $orderId = $info['id'];
        $userId = $info['userId'];
        $name = $info['name'];

        $money = $info['money'];
        $isOldCustom = $info['isOldCustom'];
        $status = 0;
        if ($type == 1) {
            $status = 2;
        }

        // 是否存在
        $t = db('adviser_user_school')
            ->where(['userId' => $userId, 'adviserId' => $adviserId, 'schoolId' => $schoolId, 'isDelete' => 0])
            ->value('id');

        Db::startTrans();
        try {
            db($table)->where(['id' => $id])->update(['adviserId' => $adviserId]);
            db('adviser_order')->insert(
                [
                    'adviserInitId' => $adviserId,
                    'adviserId' => $adviserId,
                    'userId' => $userId,
                    'name' => $name,
                    'orderId' => $orderId,
                    'orderType' => $orderType,
                    'status' => $status,
                    'schoolId' => $schoolId,
                    'createTime' => time(),
                    'isShift' => 0,
                    'money' => $money,
                    'isOldCustom' => $isOldCustom
                ]
            );
            $logId = db('adviser_order')->getLastInsID();
            operateLog('生成顾问与用户关联订单', 'adviser_order', $logId, $adminId);
            if (!$t) {
                db('adviser_user_school')->insert(
                    [
                        'userId' => $userId,
                        'adviserId' => $adviserId,
                        'schoolId' => $schoolId,
                        'createTime' => time(),
                        'isDelete' => 0
                    ]
                );
                $logId = db('adviser_user_school')->getLastInsID();
                operateLog('生成新的顾问与用户关联记录', 'adviser_user_school', $logId, $adminId);
            }
        } catch (Exception $e) {
            //dump($e->getMessage());
            Db::rollback();
            return false;
        }

        Db::commit();
        return true;
    }

    /**
     * Date: 2017-09-25
     * 优选课程
     * @param $schoolId
     * @return mixed
     */
    public function getOrderHandleCourse($schoolId)
    {
        $where['o.isDispose'] = 0;
        $where['o.schoolId'] = $schoolId;
        $where['o.status'] = 1;
        $data = db('order_class o')
            ->field('o.id,o.orderNo,o.name,o.status,o.adviserId,o.createTime,o.payDate,o.userId,u.name as uName,u.phone,an.name as adviserName,o.money,o.isOldCustom')
            ->join('user u', 'u.id=o.userId', 'LEFT')
            ->join('adviser_name an', 'an.id=o.adviserId', 'LEFT')
            ->where($where)
            ->paginate(10);
        return $data;
    }

    /**
     * Date: 2017-09-25
     * 团购订单
     * @param $schoolId
     * @return mixed
     */
    public function getOrderHandleGroup($schoolId)
    {
        $where['ob.schoolId'] = $schoolId;
        $where['ob.isDispose'] = 0;
        $where['ob.status'] = 1;
        $data = db('order_buy ob')
            ->field('ob.id,ob.orderNo,ob.name,ob.status,ob.adviserId,ob.createTime,ob.payTime,ob.userId,u.name as uName,u.phone,an.name as adviserName')
            ->join('user u', 'u.id=ob.userId', 'LEFT')
            ->join('adviser_name an', 'an.id=ob.adviserId', 'LEFT')
            ->where($where)
            ->paginate(10);
        return $data;
    }

    /**
     * Date: 2017-09-25
     * 免费体验课
     * @param $schoolId
     * @return mixed
     */
    public function getOrderHandleFreeCourse($schoolId)
    {
        $where['uc.schoolId'] = $schoolId;
//        $where['uc.adviserId'] = 0;
        $where['uc.isDispose'] = 0;
        $data = db('user_class uc')
            ->field('uc.id,0 as orderNo,cs.name,uc.status,uc.adviserId,uc.createTime,uc.userId,u.name as uName,u.phone,an.name as adviserName')
            ->join('class_school cs', 'cs.id=uc.classSchoolId', 'LEFT')
            ->join('user u', 'u.id=uc.userId', 'LEFT')
            ->join('adviser_name an', 'an.id=uc.adviserId', 'LEFT')
            ->where($where)
            ->paginate(10);
        return $data;
    }

    /**
     * Date: 2017-09-25
     * 星星券课程
     * @param $schoolId
     * @return mixed
     */
    public function getOrderHandleStarCourse($schoolId)
    {
        $where['stu.schoolId'] = $schoolId;
        $where['stu.status'] = 0;
//        $where['stu.adviserId'] = 0;
        $where['stu.isDispose'] = 0;
        $data = db('star_ticket_use stu')
            ->field('stu.id,stu.className as name,stu.status,stu.adviserId,stu.createTime,stu.userId,u.name as uName,u.phone,an.name as adviserName')
            ->join('user u', 'u.id=stu.userId', 'LEFT')
            ->join('adviser_name an', 'an.id=stu.adviserId', 'LEFT')
            ->where($where)
            ->paginate(10);
        return $data;
    }

    /**
     * Date: 2017-09-25
     * 参与星星活动第一次赠送
     * @param $schoolId
     * @return mixed
     */
    public function getOrderHandleFirstGive($schoolId)
    {
        $where['sfu.schoolId'] = $schoolId;
        $where['sfu.status'] = 0;
//        $where['sfu.adviserId'] = 0;
        $where['sfu.isDispose'] = 0;
        $data = db('star_free_use sfu')
            ->field('sfu.id,sfu.createTime,sfu.userId,sfu.adviserId,u.name as uName,u.phone,an.name as adviserName')
            ->join('adviser_name an', 'an.id=sfu.adviserId', 'LEFT')
            ->join('user u', 'u.id=sfu.userId', 'LEFT')
            ->where($where)
            ->paginate(10);
        return $data;
    }

    /**
     * Date: 2017-09-25
     * 星星兑换课程
     * @param $schoolId
     * @return mixed
     */
    public function getOrderHandleStarExchange($schoolId)
    {
        $where['su.status'] = 0;
//        $where['su.adviserId'] = 0;
        $where['su.isDispose'] = 0;
        $where['su.schoolId'] = $schoolId;
        $data = db('star_use su')
            ->field('su.id,su.starNum,su.classNum,su.className,su.adviserId,su.createTime,an.name as adviserName,u.name as uName,u.phone')
            ->join('adviser_name an', 'an.id=su.adviserId', 'LEFT')
            ->join('user u', 'u.id=su.userId', 'LEFT')
            ->where($where)
            ->paginate(10);
        return $data;
    }

    /**
     * Date: 2017-10-27
     * 万人砍订单
     * @param $schoolId
     * @return mixed
     */
    public function getOrderHandleWrkInfo($schoolId)
    {
        $where['awo.status'] = 1;
        $where['awi.isDispose'] = 0;
        $where['awi.schoolId'] = $schoolId;
        $data = db('activity_wrk_info awi')
            ->field('awi.id,awi.name as wrkName,awc.name as courseName,awi.price,awi.money,an.name as adviserName,u.name as uName,u.phone,awo.orderNo,awo.payDate,awi.createTime,awi.adviserId')
            ->join('adviser_name an', 'an.id=awi.adviserId', 'LEFT')
            ->join('activity_wrk_order awo', 'awo.id=awi.wrkOrderId', 'LEFT')
            ->join('activity_wrk_class awc', 'awc.id=awi.wrkClassId', 'LEFT')
            ->join('user u', 'u.id=awi.userId', 'LEFT')
            ->where($where)
//            ->fetchSql(true)
//            ->select();
            ->paginate(10);
        return $data;
    }

    /**
     * Date: 2017-09-26
     * 订单处理、app消息推送
     * fix Date: 2017-10-24
     * fix Content: 增加万人砍
     * @param $id
     * @param $type
     * @param $adminId
     * @return bool
     */
    public function handleOrderMessage($id, $type, $adminId)
    {
        $ret = false;
        $adviserId = 0;
        $phone = 0;
        switch ((int)$type) {
            case 1:
                // 1vip体验课2团购3优选课程4星星灯新用户免费券5星星灯领取课6星星兑换课7万人砍
                $aoInfo = db('adviser_order')->where(['orderId' => $id, 'orderType' => 3])->find();
                if (!$aoInfo) {
                    $this->assignTypeAdviserFix($type, $id, $adminId);
                }
                // 课程
                $ret = db('order_class')->where(['id' => $id])->update(['isDispose' => 1]);
                if ($ret) {
                    $adviserId = db('order_class')->where(['id' => $id])->value('adviserId');
                    operateLog('课程订单处理', 'order_class', $id, $adminId);
                }
                break;
            case 2:
                $aoInfo = db('adviser_order')->where(['orderId' => $id, 'orderType' => 2])->find();
                if (!$aoInfo) {
                    $this->assignTypeAdviserFix($type, $id, $adminId);
                }
                // 团购
                $ret = db('order_buy')->where(['id' => $id])->update(['isDispose' => 1]);
                if ($ret) {
                    $adviserId = db('order_buy')->where(['id' => $id])->value('adviserId');
                    operateLog('团购订单处理', 'order_buy', $id, $adminId);
                }
                break;
            case 3:
                $aoInfo = db('adviser_order')->where(['orderId' => $id, 'orderType' => 4])->find();
                if (!$aoInfo) {
                    $this->assignTypeAdviserFix($type, $id, $adminId);
                }
                // 被分享人参加星星灯活动首次赠送 star_free_use
                $ret = db('star_free_use')->where(['id' => $id])->update(['isDispose' => 1]);
                if ($ret) {
                    $adviserId = db('star_free_use')->where(['id' => $id])->value('adviserId');
                }
                break;
            case 4:
                $aoInfo = db('adviser_order')->where(['orderId' => $id, 'orderType' => 5])->find();
                if (!$aoInfo) {
                    $this->assignTypeAdviserFix($type, $id, $adminId);
                }
                // 星星灯点亮赠送券兑换课程 star_ticket_use
                $ret = db('star_ticket_use')->where(['id' => $id])->update(['isDispose' => 1]);
                if ($ret) {
                    $adviserId = db('star_ticket_use')->where(['id' => $id])->value('adviserId');
                }
                break;
            case 5:
                $aoInfo = db('adviser_order')->where(['orderId' => $id, 'orderType' => 6])->find();
                if (!$aoInfo) {
                    $this->assignTypeAdviserFix($type, $id, $adminId);
                }
                // 星星兑换课程 star_use
                $ret = db('star_use')->where(['id' => $id])->update(['isDispose' => 1]);
                if ($ret) {
                    $adviserId = db('star_use')->where(['id' => $id])->value('adviserId');
                }
                break;
            case 6:
                $aoInfo = db('adviser_order')->where(['orderId' => $id, 'orderType' => 1])->find();
                if (!$aoInfo) {
                    $this->assignTypeAdviserFix($type, $id, $adminId);
                }
                // 免费体验课 user_class
                $ret = db('user_class')->where(['id' => $id])->update(['isDispose' => 1]);
                if ($ret) {
                    $adviserId = db('user_class')->where(['id' => $id])->value('adviserId');
                }
                break;
            case 7:
                $aoInfo = db('adviser_order')->where(['orderId' => $id, 'orderType' => 7])->find();
                if (!$aoInfo) {
                    $this->assignTypeAdviserFix($type, $id, $adminId);
                }
                // 万人砍 activity_wrk_info
                $ret = db('activity_wrk_info')->where(['id' => $id])->update(['isDispose' => 1]);
                if ($ret) {
                    $adviserId = db('activity_wrk_info')->where(['id' => $id])->value('adviserId');
                }
                break;
            default:
                operateLog('极光推送Failure', 'xxx', 0, $adminId);
                return false;
                break;
        }

        if ($adviserId) {
            $phone = db('adviser_name ad')
                ->join('user u', 'u.id=ad.userId', 'LEFT')
                ->where(['ad.id' => $adviserId])
                ->value('u.phone');
            if ($phone) {
                $Push = new Jpush();
                $m_time = 86400;
                $message = '';

                $receive['alias'] = [$phone];
                $content = '您有一个新的客户!';
                $arr = [];
                $arr['info'] = '您有一个新的客户!';
                $arr['data'] = ['type' => 'assignAdviser'];
                $type = 4;
                $j = $Push->push($receive, $content, $arr, $type, $m_time);
                if ($j) {
                    operateLog('极光推送 To ' . $phone, '', 0, $adminId);
                } else {
                    operateLog('失败 极光推送 To ' . $phone, 'xxx', 0, $adminId);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Date: 2017-11-01
     * 已有顾问 直接处理订单 生成顾问关联表
     * @param $type
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function assignTypeAdviserFix($type, $id, $adminId)
    {
        // 指定订单类型分配顾问
        switch ((int)$type) {
            case 1:
                $table = 'order_class';
                $field = 'id,userId,name,adviserId,schoolId,money,isOldCustom';
                $orderType = 3;
                break;
            case 2:
                $table = 'order_buy';
                $field = 'id,userId,"体验课" as name,adviserId,schoolId,money,2 as isOldCustom';
                $orderType = 2;
                break;
            case 3:
                $table = 'star_free_use';
                $field = 'id,userId,"体验课" as name,adviserId,schoolId,o as money,2 as isOldCustom';
                $orderType = 4;
                break;
            case 4:
                $table = 'star_ticket_use';
                $field = 'id,userId,className as name,adviserId,schoolId,o as money,2 as isOldCustom';
                $orderType = 5;
                break;
            case 5:
                $table = 'star_use';
                $field = 'id,userId,className as name,adviserId,schoolId,o as money,2 as isOldCustom';
                $orderType = 6;
                break;
            case 6:
                $table = 'user_class';
                $field = 'id,userId,"体验课" as name,adviserId,schoolId,0 as money,2 as isOldCustom';
                $orderType = 1;
                break;
            case 7:
                $table = 'activity_wrk_info';
                $field = 'id,userId,name,adviserId,schoolId,price as money,2 as isOldCustom';
                $orderType = 7;
                break;
            default:
                return false;
                break;
        }

        //  订单状态 0未体验1已体验2未签约3已签约（0,1体验课用/3,4优选课程用）
        //  订单类别 1vip体验课2团购3优选课程4星星灯新用户免费券5星星灯领取课6星星兑换课7万人砍
        $info = db($table)->field($field)->where(['id' => $id])->find();
        if (!$info) {
            return false;
        }
        $orderId = $info['id'];
        $userId = $info['userId'];
        $name = $info['name'];
        $status = 0;
        if ($type == 1) {
            $status = 2;
        }

        $adviserId = $info['adviserId'];
        $schoolId = $info['schoolId'];
        $orderMoney = $info['money'];
        $isOldCustom = $info['isOldCustom'];

        // 是否存在
        $t = db('adviser_user_school')
            ->where(['userId' => $userId, 'adviserId' => $adviserId, 'schoolId' => $schoolId, 'isDelete' => 0])
            ->value('id');

        Db::startTrans();
        try {
            db($table)->where(['id' => $id])->update(['adviserId' => $adviserId]);
            db('adviser_order')->insert(
                [
                    'adviserInitId' => $adviserId,
                    'adviserId' => $adviserId,
                    'userId' => $userId,
                    'name' => $name,
                    'orderId' => $orderId,
                    'orderType' => $orderType,
                    'status' => $status,
                    'schoolId' => $schoolId,
                    'createTime' => time(),
                    'isShift' => 0,
                    'money' => $orderMoney,
                    'isOldCustom' => $isOldCustom
                ]
            );
            $logId = db('adviser_order')->getLastInsID();
            operateLog('生成顾问与用户关联订单', 'adviser_order', $logId, $adminId);
            if (!$t) {
                db('adviser_user_school')->insert(
                    [
                        'userId' => $userId,
                        'adviserId' => $adviserId,
                        'schoolId' => $schoolId,
                        'createTime' => time(),
                        'isDelete' => 0
                    ]
                );
                $logId = db('adviser_user_school')->getLastInsID();
                operateLog('生成新的顾问与用户关联记录', 'adviser_user_school', $logId, $adminId);
            }
        } catch (Exception $e) {
//            dump($e->getMessage());
            Db::rollback();
            return false;
        }

        Db::commit();
        return true;
    }

}