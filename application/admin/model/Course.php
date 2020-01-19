<?php
/**
 * User: LiuTong
 * Date: 2017-07-25
 * Time: 16:56
 */

namespace app\admin\model;

use app\common\model\CourseCommon;
use app\common\model\ShopCommon;
use think\Db;
use think\Exception;
use think\Model;

class Course extends Model
{

    /**
     * 课程类别
     * Date: 2017-07-25
     * @return array
     */
    public function getCourseTypeList()
    {
        $data = [];
        $CourseCommon = new CourseCommon();
        $levelOne = $CourseCommon->CourseTypesLevel1();
        $levelTwo = $CourseCommon->allCourseLevelTwo();

        if ($levelOne) {
            $data['levelOne'] = $levelOne;
            if ($levelTwo) {
                foreach ($levelOne as $k => $v) {
                    foreach ($levelTwo as $ke => $vo) {
                        if ($vo['typeId'] == $v['id']) {
                            $data['levelTwo'][] = ['one' => $v['name'], 'two' => $vo['name'], 'id' => $vo['id'], 'createTime' => $vo['createTime']];
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 课程类型修改保存
     * Date: 2017-07-26
     * @param $id
     * @param $name
     * @param $adminId
     * @return bool
     */
    public function saveCourseTypeEdit($id, $name, $adminId)
    {
        try {
            $data['name'] = $name;
            $data['createTime'] = time();

            db('class_type')->where(['id' => $id])->update($data);
            operateLog('修改课程类别', 'class_type', $id, $adminId);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * 是否具有子类别
     * Date: 2017-07-26
     * @param $id
     * @return bool
     */
    public function ifHaveSubType($id)
    {
        $num = db('class_type')->where(['typeId' => $id, 'isDelete' => 0])->count('id');
        if ($num > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 课程类别删除
     * Date: 2017-07-26
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function delCourseType($id, $adminId)
    {
        $data['isDelete'] = 1;
        $res = db('class_type')->where(['id' => $id])->update($data);
        operateLog('删除课程类别', 'class_type', $id, $adminId);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 课程类别新增
     * Date: 2017-07-26
     * @param $data
     * @return bool
     */
    public function saveCourseType($data)
    {
        try {
            $data['createTime'] = time();
            $data['isDelete'] = 0;
            db('class_type')->insert($data);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 课程审核列表
     * Date: 2017-07-26
     * fix Date: 2017-09-13
     * fix Content: 状态筛选
     * @param int $status
     * @param $schoolId
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function courseVerifyList($status = -1, $schoolId = -1)
    {
        $where = [];
        if ($schoolId != -1) {
            $where['c.schoolId'] = $schoolId;
        }

        if ($status != -1) {
            $where['c.status'] = $status;
        } else {
            $where['c.status'] = ['<>', 3];
        }
        $where['c.isDelete'] = 0;
        $data = db('class_school')
            ->alias('c')
            ->field('c.id,c.name,c.classTime,c.startAge,c.endAge,c.money,c.status,c.createTime,ct.name as typeName,c.adminTime,a.name as adminName,c.isStar,sc.name schoolName,c.starNum,c.exchangeNum')
            ->join('class_type ct', 'ct.id=c.typeId', 'LEFT')
            ->join('admin a', 'a.id=c.adminId', 'LEFT')
            ->join('school sc', 'sc.id=c.schoolId', 'LEFT')
            ->where($where)
            ->order('c.status asc,c.createTime asc')
            ->paginate(10, false, ['query' => ['status' => $status, 'schoolId' => $schoolId]]);
        return $data;
    }

    /**
     * Date: 2017-11-17
     * 校区列表
     * @return mixed
     */
    public function schoolList()
    {
        $data = db('school')->field('id,name')->where(['status' => 1])->select();
        return $data;
    }

    /**
     * 课程审核拒绝
     * Date: 2017-07-26
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function refuseCourse($id, $adminId)
    {
        try {
            $data['adminId'] = $adminId;
            $data['adminTime'] = time();
            $data['status'] = 4;
            db('class_school')->where(['id' => $id])->update($data);
            operateLog('课程审核->拒绝', 'class_school', $id, $adminId);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * 查询课程分成返现
     * Date: 2017-07-26
     * @param $classSchoolId
     * @return array|false|\PDOStatement|string|\think\Model
     *
     * fix Date: 2017-09-26
     * fix Content: 增加受益人金额
     */
    public function getCourseRule($classSchoolId)
    {
        $data = db('class_rule')
//            ->field('id,classSchoolId,name,money,againMoney,lZero,lOne,lTwo,lThree,lFour,adviser,referrer,shopOne,shopTwo,benefitMoney')
            ->field('*')
            ->where(['classSchoolId' => $classSchoolId, 'isDelete' => 0])
            ->find();
        return $data;
    }

    /**
     * 课程规则新增、修改
     * Date: 2017-07-26
     * @param $data
     * @param $id
     * @param $adminId
     * @return int|string
     */
    public function saveCourseRule($data, $id, $adminId)
    {
        $data['createTime'] = time();
        $data['isDelete'] = 0;

        $shopCommon = new ShopCommon();

        Db::startTrans();
        try {
            if ($id) {
                Db::name('class_rule')->where(['id' => $id])->update(['isDelete' => 1]);
                operateLog('更新校区课程规则', 'class_rule', $id, $adminId);
            }
            Db::name('class_rule')->insert($data);

            $logID = Db::name('class_rule')->getLastInsID();
            operateLog('新增校区课程规则', 'class_rule', $logID, $adminId);

            $pData['status'] = 2;
            $pData['adminTime'] = time();
            $pData['adminId'] = $data['adminId'];
            Db::name('class_school')->where(['id' => $data['classSchoolId']])->update($pData);
            operateLog('更新校区课程信息->审核课程', 'class_school', $data['classSchoolId'], $adminId);
            if (!$id) {
                // 新增审核 增加商户以及品牌课程数
                $ret = $shopCommon->updateSchoolCourseNum($data['classSchoolId']);
                if (!$ret) {
                    Db::rollback();
                    return false;
                }
            }
        } catch (\Exception $e) {
//            dump($e->getMessage());
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

    /**
     * Date: 2017-11-10
     * 获得校区课程规则
     * @param $classSchoolId
     * @return array
     */
    public function getCourseRuleNew($classSchoolId)
    {
        $data = db('class_rule')->where(['classSchoolId' => $classSchoolId, 'isDelete' => 0])->select();
        $returnData = [];
        if (count($data) > 1) {
            $returnData['one'] = $data[0];
            $returnData['one']['miniMoneyOne'] = $data[0]['money'] - $data[0]['shopMoney'] - $data[0]['referrer'] - $data[0]['benefitMoney'] - $data[0]['lZero'] - $data[0]['lTwo'];
            $returnData['two'] = $data[1];
            $returnData['two']['miniMoneyTwo'] = $data[1]['againMoney'] - $data[1]['shopMoney'] - $data[1]['referrer'] - $data[1]['benefitMoney'] - $data[1]['lZero'] - $data[1]['lTwo'];
        } else {
            if (!empty($data)) {
                $returnData = $data[0];
                $returnData['miniMoney'] = $data[0]['money'] - $data[0]['shopMoney'] - $data[0]['referrer'] - $data[0]['benefitMoney'] - $data[0]['lZero'] - $data[0]['lTwo'];
            }
        }
        return $returnData;
    }

    /**
     * Data: 2017-11-10
     * 课程审核
     * @param $data
     * @param $adminId
     * @return bool
     */
    public function saveCourseRuleNew($data, $adminId)
    {
        $ruleInfo = db('class_rule')->where(['classSchoolId' => $data['classSchoolId'], 'isDelete' => 0])->select();
        $courseInfo = db('class_school')->where(['id' => $data['classSchoolId']])->find();
        Db::startTrans();
        try {
            if (!empty($ruleInfo)) {
                db('class_rule')->where(['classSchoolId' => $data['classSchoolId']])->update(['isDelete' => 1]);
                operateLog('删除旧的校区课程审核记录', 'class_rule', 0, $adminId);
            }
            $insertData = [
                [
                    'classSchoolId' => $data['classSchoolId'],
                    'name' => $courseInfo['name'],
                    'money' => $courseInfo['money'],
                    'againMoney' => $courseInfo['money'],
                    'lZero' => $data['lZeroOne'],
                    'lOne' => $data['lOneOne'],
                    'lTwo' => $data['lTwoOne'],
                    'lThree' => $data['lTwoOne'],
                    'lFour' => $data['lTwoOne'],
                    'adviser' => 0,
                    'referrer' => $data['referrerMoneyOne'],
                    'shopMoney' => $data['shopMoneyOne'],
                    'benefitMoney' => $data['benefitMoneyOne'],
                    'createTime' => time(),
                    'adminId' => $adminId,
                    'isDelete' => 0,
                    'isOldCustom' => 2,
                ],
                [
                    'classSchoolId' => $data['classSchoolId'],
                    'name' => $courseInfo['name'],
                    'money' => $courseInfo['money'],
                    'againMoney' => $data['againMoneyTwo'],
                    'lZero' => $data['lZeroTwo'],
                    'lOne' => $data['lOneTwo'],
                    'lTwo' => $data['lTwoTwo'],
                    'lThree' => $data['lTwoTwo'],
                    'lFour' => $data['lTwoTwo'],
                    'adviser' => 0,
                    'referrer' => $data['referrerMoneyTwo'],
                    'shopMoney' => $data['shopMoneyTwo'],
                    'benefitMoney' => $data['benefitMoneyTwo'],
                    'createTime' => time(),
                    'adminId' => $adminId,
                    'isDelete' => 0,
                    'isOldCustom' => 1,
                ]
            ];
            db('class_rule')->insertAll($insertData);
            operateLog('新增校区课程审核记录', 'class_rule', 0, $adminId);
            if (empty($ruleInfo)) {
                db('shop')->where(['id' => $courseInfo['shopId']])->setInc('classNum');
                operateLog('新增课程审核通过增加所属商户课程数', 'shop', $courseInfo['shopId'], $adminId);
                db('brand')->where(['id' => $courseInfo['brandId']])->setInc('classNum');
                operateLog('新增课程审核通过增加所属品牌课程数', 'brand', $courseInfo['brandId'], $adminId);
            }
            db('class_school')->where(['id' => $data['classSchoolId']])->update(['status' => 2]);
            operateLog('课程审核通过', 'class_school', $data['classSchoolId'], $adminId);
        } catch (Exception $e) {
            //dump($e->getMessage());die;
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

    /**
     * Data: 2017-11-10
     * 老用户续费课程审核
     * @param $data
     * @param $adminId
     * @return bool
     */
    public function saveOldCourseRuleNew($data, $adminId)
    {
        $ruleInfo = db('class_rule')->where(['classSchoolId' => $data['classSchoolId'], 'isDelete' => 0])->find();
        $courseInfo = db('class_school')->where(['id' => $data['classSchoolId']])->find();
        Db::startTrans();
        try {
            if (!empty($ruleInfo)) {
                db('class_rule')->where(['classSchoolId' => $data['classSchoolId']])->update(['isDelete' => 1]);
                operateLog('删除旧的校区老用户续费课程审核记录', 'class_rule', $ruleInfo['id'], $adminId);
            }
            $insertData =
                [
                    'classSchoolId' => $data['classSchoolId'],
                    'name' => $courseInfo['name'],
                    'money' => $courseInfo['money'],
                    'againMoney' => $courseInfo['money'],
                    'lZero' => $data['lZero'],
                    'lOne' => $data['lOne'],
                    'lTwo' => $data['lTwo'],
                    'lThree' => $data['lTwo'],
                    'lFour' => $data['lTwo'],
                    'adviser' => 0,
                    'referrer' => $data['referrerMoney'],
                    'shopMoney' => $data['shopMoney'],
                    'benefitMoney' => $data['benefitMoney'],
                    'createTime' => time(),
                    'adminId' => $adminId,
                    'isDelete' => 0,
                    'isOldCustom' => 1,
                ];
            db('class_rule')->insert($insertData);
            operateLog('新增校区老用户续费课程审核记录', 'class_rule', 0, $adminId);
            if (empty($ruleInfo)) {
                db('shop')->where(['id' => $courseInfo['shopId']])->setInc('classNum');
                operateLog('新增校区老用户续费课程审核通过增加所属商户课程数', 'shop', $courseInfo['shopId'], $adminId);
                db('brand')->where(['id' => $courseInfo['brandId']])->setInc('classNum');
                operateLog('新增校区老用户续费课程审核通过增加所属品牌课程数', 'brand', $courseInfo['brandId'], $adminId);
            }
            db('class_school')->where(['id' => $data['classSchoolId']])->update(['status' => 2]);
            operateLog('校区老用户续费课程审核通过', 'class_school', $data['classSchoolId'], $adminId);
        } catch (Exception $e) {
            //dump($e->getMessage());die;
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

    /**
     * 查询校区课程详情
     * Date: 2017-07-26
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     */
    public function getCourseDetail($id)
    {
        $data = db('class_school cs')
            ->field('cs.*,s.name as shopName,sc.name as schoolName')
            ->join('shop s', 's.id=cs.shopId', 'LEFT')
            ->join('school sc', 'sc.id=cs.shopId', 'LEFT')
            ->where(['cs.id' => $id])
            ->find();
        return $data;
    }

    /**
     * 课程订单列表
     * Date: 2017-08-10
     * fix Date: 2017-08-31
     * fix Content: 分页
     * @param string $orderNo
     * fix Date: 2017-09-13
     * fix Content: 状态筛选、手机号
     * @param int $status
     * @param int $phone
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function courseOrderList($orderNo = '', $status = -1, $phone = 0)
    {
        if ($orderNo) {
            $where['o.orderNo'] = $orderNo;
        }
        $where['o.isDelete'] = 0;
//        $where['o.status'] = ['exp', '<>0'];
        if ($status != -1) {
            $where['o.status'] = $status;
        }

        if ($phone != 0) {
            $where['u.phone'] = $phone;
        }
        $data = db('order_class')
            ->alias('o')
            ->field('o.id,o.orderNo,o.money,o.createTime,o.name,o.status,o.isSign,u.name as uName,u.phone')
            ->join('user u', 'u.id=o.userID', 'LEFT')
            ->where($where)
            ->paginate(10, false, ['query' => ['orderNo' => $orderNo]]);
        return $data;
    }

    /**
     * Date: 2017-09-26
     * 指定星星课程
     * @return mixed
     */
    public function getStarCourse()
    {
        $where['cs.isStar'] = 1;
        $data = db('class_school cs')
            ->field('cs.id,cs.name,cs.money,cs.startAge,cs.endAge,cs.isStar,cs.starNum,s.name as shopName,sc.name as schoolName,ct.name as typeName,cs.exchangeNum')
            ->join('class_type ct', 'ct.id=cs.typeId', 'LEFT')
            ->join('shop s', 's.id=cs.shopId', 'LEFT')
            ->join('school sc', 'sc.id=cs.schoolId', 'LEFT')
            ->where($where)
            ->paginate(10);
        return $data;
    }

    /**
     * Date: 2017-09-26
     * 修改兑换课程星星数
     * @param $id
     * @param $data
     * @param $adminId
     * @return bool
     * @internal param $starNum
     */
    public function changeStarCourse($id, $data, $adminId)
    {
        $where['id'] = $id;
        $ret = db('class_school')->where($where)->update(
            [
                'starNum' => $data['starNum'],
                'exchangeNum' => $data['exchangeNum']
            ]
        );
        if ($ret) {
            operateLog('修改课程星星兑换数量', 'class_school', $id, $adminId);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Date: 2017-09-27
     * 去除星星课程标识和兑换星星数
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function starCourseRemove($id, $adminId)
    {
        $ret = db('class_school')->where(['id' => $id])->update(['isStar' => 0, 'starNum' => 0, 'exchangeNum' => 0]);
        if ($ret) {
            operateLog('去除星星课程标识、兑换星星数、奖励星星数', 'class_school', $id, $adminId);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Date: 2017-09-27
     * 课程设置为星星灯活动课兑换并设置所需兑换的星星数
     * @param $id
     * @param $data
     * @param $adminId
     * @return bool
     */
    public function addStarCourse($id, $data, $adminId)
    {
        $ret = db('class_school')->where(['id' => $id])->update(
            [
                'starNum' => $data['starNum'],
                'exchangeNum' => $data['exchangeNum'],
                'isStar' => $data['isStar']
            ]
        );
        if ($ret) {
            operateLog('课程参加星星灯活动，设置兑换星星数、奖励星星数以及是否参与星星灯活动', 'class_school', $id, $adminId);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Date: 2017-10-24
     * 课程星星情况
     * @param $id
     * @return mixed
     */
    public function courseStarGet($id)
    {
        $data = db('class_school')->field('isStar,starNum,exchangeNum')->where(['id' => $id])->find();
        return $data;
    }

    /**
     * 课程审核列表
     * Date: 2017-07-26
     * fix Date: 2017-09-13
     * fix Content: 状态筛选
     * @param int $status
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function courseVerifyListWrk($status = -1)
    {
        $where = [];
        if ($status != -1) {
            $where['awc.status'] = $status;
        }
        $where['awc.isDelete'] = 0;
        $data = db('activity_wrk_class awc')
            ->field('awc.id,awc.name,awc.price,awc.money,awc.status,awc.createTime,awc.verifyAdminId,awc.verifyTime,sc.name as schoolName,a.name as adminName,awc.isStar,awc.starNum')
            ->join('admin a', 'a.id=awc.verifyAdminId', 'LEFT')
            ->join('school sc', 'sc.id=awc.schoolId', 'LEFT')
            ->where($where)
            ->order('awc.status asc,awc.createTime asc')
            ->paginate(10, false);
        return $data;
    }

    /**
     * Date: 2017-10-30
     * 万人砍课程星星情况
     * @param $id
     * @return mixed
     */
    public function courseStarGetWrk($id)
    {
        $data = db('activity_wrk_class')->field('isStar,starNum')->where(['id' => $id])->find();
        return $data;
    }

    /**
     * Date: 2017-10-30
     * 万人砍课程 设置 赠送星星数
     * @param $id
     * @param $data
     * @param $adminId
     * @return bool
     */
    public function addStarCourseWrk($id, $data, $adminId)
    {
        $ret = db('activity_wrk_class')->where(['id' => $id])->update(
            [
                'starNum' => $data['starNum'],
                'isStar' => $data['isStar']
            ]
        );
        if ($ret) {
            operateLog('万人砍课 星星灯活动，设置奖励星星数以及是否参与星星灯活动', 'activity_wrk_class', $id, $adminId);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 万人砍课程审核拒绝
     * Date: 2017-10-31
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function refuseCourseWrk($id, $adminId)
    {
        try {
            $data['verifyAdminId'] = $adminId;
            $data['verifyTime'] = time();
            $data['status'] = 3;
            db('activity_wrk_class')->where(['id' => $id])->update($data);
            operateLog('万人砍课程审核->拒绝', 'activity_wrk_class', $id, $adminId);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Date: 2017-10-31
     * 获取万人砍课程信息
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     */
    public function getCourseRuleWrk($id)
    {
        $data = db('activity_wrk_class')->where(['id' => $id])->find();
        return $data;
    }

    /**
     * Date: 2017-10-31
     * 万人砍课程规则
     * @param $id
     * @param $adminId
     * @param $data
     * @return bool
     */
    public function saveCourseRuleWrk($id, $adminId, $data)
    {
        $shopMoney = db('activity_wrk_class')->where(['id' => $id])->value('shopMoney');
        $data['verifyAdminId'] = $adminId;
        $data['verifyTime'] = time();
        $data['status'] = 2;
        $ret = db('activity_wrk_class')->where(['id' => $id])->update($data);
        if (!$ret) {
            return false;
        }
        if ($shopMoney) {
            operateLog('万人砍课程二次审核', 'activity_wrk_class', $id, $adminId);
        } else {
            operateLog('万人砍课程审核', 'activity_wrk_class', $id, $adminId);
        }
        return true;
    }

    /**
     * Date: 2017-10-31
     * 万人砍课程订单
     * @param $orderNo
     * @param $status
     * @param $phone
     * @return \think\Paginator
     */
    public function getCourseOrderWrk($orderNo, $status, $phone)
    {
        $where['awo.status'] = ['>', 0];
        if ($orderNo) {
            $where['awo.orderNo'] = $orderNo;
        }
        if ($phone) {
            $where['u.phone'] = $phone;
        }
        if ($status) {
            $where['awo.status'] = $status;
        }
        $data = db('activity_wrk_order awo')
            ->field('awo.*,u.name as uName,u.phone')
            ->join('user u', 'u.id=awo.userId', 'LEFT')
            ->where($where)
            ->paginate(10, false);
        return $data;
    }

    /**
     * Date: 2017-10-31
     * 万人砍订单课程
     * @param $id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getCourseOrderDetailWrk($id)
    {
        $where['awi.wrkOrderId'] = $id;
        $data = db('activity_wrk_info awi')
            ->field('awi.name,awi.money,sc.name as schoolName,awi.signTime')
            ->join('school sc', 'sc.id=awi.schoolId', 'LEFT')
            ->where($where)
            ->select();
        if ($data) {
            foreach ($data as $k => $v) {
                if ($v['signTime']) {
                    $data[$k]['signTime'] = date('Y-m-d H:i');
                } else {
                    $data[$k]['signTime'] = '未签约';
                }
            }
        }
        return $data;
    }

    /**
     * Date: 2017-11-06
     * 获取课程退款金额
     * @param $orderId
     * @return mixed
     */
    public function courseBackMoneyGet($orderId)
    {
        $data = db('order_class')->field('money,userMoney')->where(['id' => $orderId])->find();
        return $data;
    }

    /**
     * Date: 2017-11-06
     * 退款申请
     * @param $orderId
     * @param $data
     * @return bool
     */
    public function applyCourseBackMoney($orderId, $data)
    {
        $orderData = db('order_class')->field('orderNo,userId,money,userMoney')->where(['id' => $orderId])->find();
        if (empty($orderData)) {
            return false;
        }
        $data['money'] = $orderData['money'] - $orderData['userMoney'];
        $data['orderNo'] = $orderData['orderNo'];
        $data['userId'] = $orderData['userId'];
        $data['isReview'] = 0;
        $data['createTime'] = time();
        $data['isDelete'] = 0;
        $data['orderType'] = 1;

        Db::startTrans();
        try {
            db('back')->insert($data);
            db('order_class')->where(['id' => $orderId])->update(['status' => 2]);
            operateLog('优选课程退款申请', 'order_class', $orderId, $data['adminId']);
        } catch (Exception $e) {
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

    /**
     * Date: 2017-11-06
     * 检测子单是否有已签约
     * @param $orderId
     * @return bool
     */
    public function courseBackMoneyStatusWrk($orderId)
    {
        $data = db('activity_wrk_info')->field('isSign')->where(['wrkOrderId' => $orderId])->select();
        foreach ($data as $k => $v) {
            if ($v['isSign'] == 1) {
                return false;
            }
        }
        return true;
    }

    /**
     * Date: 2017-11-06
     * 获取万人砍课程退款金额
     * @param $orderId
     * @return mixed
     */
    public function courseBackMoneyGetWrk($orderId)
    {
        $data = db('activity_wrk_order')->field('money')->where(['id' => $orderId])->find();
        return $data;
    }

    /**
     * Date: 2017-11-06
     * 万人砍退款申请
     * @param $orderId
     * @param $data
     * @return bool
     */
    public function applyCourseBackMoneyWrk($orderId, $data)
    {
        $orderData = db('activity_wrk_order')->field('orderNo,userId,money')->where(['id' => $orderId])->find();
        if (empty($orderData)) {
            return false;
        }
        $data['money'] = $orderData['money'];
        $data['orderNo'] = $orderData['orderNo'];
        $data['userId'] = $orderData['userId'];
        $data['isReview'] = 0;
        $data['createTime'] = time();
        $data['isDelete'] = 0;
        $data['orderType'] = 2;

        Db::startTrans();
        try {
            db('back')->insert($data);
            db('activity_wrk_order')->where(['id' => $orderId])->update(['status' => 2]);
            operateLog('万人砍课程退款申请', 'activity_wrk_order', $orderId, $data['adminId']);
        } catch (Exception $e) {
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

}