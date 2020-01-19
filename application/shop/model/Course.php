<?php
/**
 * User: LiuTong
 * Date: 2017-07-20
 * Time: 11:30
 */

namespace app\shop\model;

use app\common\model\ShopCommon;
use think\Db;
use think\Exception;
use think\Model;

class Course extends Model
{

    /**
     * 课程列表
     * Date: 2017-07-20
     * @param $adminId
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getCourseList($adminId)
    {
        $shop = new ShopCommon();
        $shopId = $shop->getShopIdFromAdminId($adminId);

        $data = db('class')
            ->alias('c')
            ->field('c.id,c.name,c.classTime,c.content,c.createTime,c.typeId,ct.name as typeName,c.img,c.money')
            ->join('class_type ct', 'ct.id=c.typeId', 'LEFT')
            ->where(['c.shopId' => $shopId, 'c.isDelete' => 0])
            ->paginate(10, false);
//            ->select();
        return $data;
    }

    /**
     * 商户课程模板是否有校区仍在使用
     * @param $courseTemplateId
     * @return bool
     */
    public function schoolIfStillUseThisCourse($courseTemplateId)
    {
        $ret = db('class_school')
            ->where(['classId' => $courseTemplateId, 'isDelete' => 0])
            ->count();
        if ($ret > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 删除商户课程模板
     * @param $id
     * @return int|string
     */
    public function shopCourseDel($id)
    {
        $ret = db('class')->where(['id' => $id])->update(['isDelete' => 1]);
        return $ret;
    }

    /**
     * 课程新增
     * Date: 2017-07-21
     * fix Date: 2017-11-01
     * fix Content: 返回id
     * @param $adminId
     * @param $data
     * @return bool
     */
    public function shopCourseAdd($adminId, $data)
    {
        $shopCommon = new ShopCommon();
        $shop = $shopCommon->getBrandIdShopIdFromAdminId($adminId);
        $data['shopId'] = $shop['shopId'];
        $data['brandId'] = $shop['brandId'];
        $data['createTime'] = time();
        $data['isDelete'] = 0;

        $ret = db('class')->insertGetId($data);
        if ($ret) {
            return $ret;
        } else {
            return false;
        }
    }

    /**
     * 课程详情
     * Date: 2017-07-21
     * @param $id
     * @return array|bool|false|\PDOStatement|string|Model
     */
    public function courseDetail($id)
    {
        if (!is_numeric($id)) {
            return false;
        }
        $data = db('class')
            ->alias('c')
            ->field('c.*,ct.name as typeName')
            ->join('class_type ct', 'ct.id=c.typeId', 'LEFT')
            ->where(['c.id' => $id])
            ->find();
        return $data;
    }

    /**
     * 查询课程模板 校区使用列表
     * Date: 2017-07-21
     * @param $classId
     * @return bool|false|\PDOStatement|string|\think\Collection
     */
    public function getCourseSchoolList($classId)
    {
        if (!is_numeric($classId)) {
            return false;
        }
        $data = db('class_school')
            ->alias('cs')
            ->field('cs.*,ct.name as typeName,s.name as schoolName')
            ->join('class_type ct', 'ct.id=cs.typeId', 'LEFT')
            ->join('school s', 's.id=cs.schoolId', 'LEFT')
            ->where(['cs.classId' => $classId, 'cs.isDelete' => 0])
            ->paginate(10, false);
//            ->select();

        /*
        if ($data) {
//            状态0未审核1审核中2审核通过(上架)3下架
            foreach ($data as $k => $v) {
                switch ($v['status']) {
                    case 0:
                        $data[$k]['statusName'] = '未审核';
                        break;
                    case 1:
                        $data[$k]['statusName'] = '待审核';
                        break;
                    case 2:
                        $data[$k]['statusName'] = '审核通过';
                        break;
                    case 3:
                        $data[$k]['statusName'] = '已下架';
                        break;
                    case 4:
                        $data[$k]['statusName'] = '拒绝';
                        break;
                }
            }
        }
        */
        return $data;
    }

    /**
     * 课程修改
     * Date: 2107-07-21
     * @param $classId
     * @param $data
     * @return bool
     */
    public function shopCourseEdit($classId, $data)
    {
        $data['createTime'] = time();

        $ret = db('class')->where(['id' => $classId])->update($data);
        if ($ret) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 校区课程保存
     * Date: 2017-07-24
     * @param $data
     * @param $adminId
     * @return bool
     */
    public function schoolCourseSave($data, $adminId)
    {
        $ShopCommon = new ShopCommon();
        $brandIdSchoolId = $ShopCommon->getBrandIdShopIdFromAdminId($adminId);
        $data['brandId'] = $brandIdSchoolId['brandId'];
        $data['shopId'] = $brandIdSchoolId['shopId'];
        $data['createTime'] = time();

        $data['status'] = 0;
        $data['isDelete'] = 0;

        $ret = db('class_school')->insert($data);
        if ($ret) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Date: 2017-10-24
     * 校区课程详情
     * @param $id
     * @return mixed
     */
    public function getSchoolCourseDetail($id)
    {
        $data = db('class_school')
            ->alias('cs')
            ->field('cs.*,ct.name as typeName')
            ->join('class_type ct', 'ct.id=cs.typeId', 'LEFT')
            ->where(['cs.id' => $id])
            ->find();
        return $data;
    }

    /**
     * Date: 2017-11-01
     * 下架校区课程
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function downSchoolCourse($id, $adminId)
    {
        $info = db('class_school')->where(['id' => $id])->find();
        if (empty($info)) {
            return false;
        }
        $brandHome = db('brand_home')->where(['brandId' => $info['brandId']])->find();
        $brand = db('brand')->where(['id' => $info['brandId']])->find();
        try {
            db('class_school')->where(['id' => $id])->update(['status' => 3]);
            operateLog('下架校区课程', 'class_school', $id, $adminId);
            db('shop')->where(['id' => $info['shopId']])->setDec('classNum');
            operateLog('减去商户课程数', 'shop', $info['shopId'], $adminId);
            db('brand')->where(['id' => $info['brandId']])->setDec('classNum');
            operateLog('减去品牌课程数', 'brand', $info['brandId'], $adminId);
            if ($brandHome && ($brand['classNum'] == 1)) {
                db('brand_home')->where(['id' => $brandHome['id']])->delete();
                operateLog('减去品牌课程数', 'brand_home', $brandHome['id'], $adminId);
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Date: 2017-11-01
     * 删除校区课程
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function deleteSchoolCourse($id, $adminId)
    {
        $info = db('class_school')->where(['id' => $id])->find();
        if (empty($info)) {
            return false;
        }
        try {
            db('class_school')->where(['id' => $id])->update(['isDelete' => 1]);
            operateLog('删除校区课程', 'class_school', $id, $adminId);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Date: 2017-11-09
     * 修改校区课程信息
     * @param $data
     * @param $classId
     * @param $adminId
     * @return bool
     */
    public function saveCourseSchoolEdit($data, $classId, $adminId)
    {
        Db::startTrans();
        try {
            db('class_school')->where(['id' => $classId])->update($data);
            operateLog('修改校区课程信息', 'class_school', $classId, $adminId);
        } catch (Exception $e) {
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

    /**
     * Date: 2017-11-09
     * 重新上架校区课程
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function reUpSchoolCourse($id, $adminId)
    {
        Db::startTrans();
        try {
            db('class_school')->where(['id' => $id])->update(['status' => 0]);
            operateLog('重新提交校区课程审核', 'class_school', $id, $adminId);
        } catch (Exception $e) {
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

}