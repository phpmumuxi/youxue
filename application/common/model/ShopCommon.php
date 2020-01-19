<?php
/**
 * User: LiuTong
 * Date: 2017-07-20
 * Time: 11:43
 */

namespace app\common\model;

use think\Db;
use think\Model;

class ShopCommon extends Model
{

    /**
     * 根据管理员id查询其管理的商户id
     * Date: 2017-07-20
     * @param $adminId
     * @return mixed
     */
    public function getShopIdFromAdminId($adminId)
    {
        $shopId = db('admin')->where(['id' => $adminId])->value('shopId');
        return $shopId;
    }

    /**
     * 根据管理员id查询其管理的校区id
     * Date: 2017-07-20
     * @param $adminId
     * @return mixed
     */
    public function getSchoolIdFromAdminId($adminId)
    {
        $schoolId = db('admin')->where(['id' => $adminId])->value('schoolId');
        return $schoolId;
    }

    /**
     * 根据管理员id获取品牌id、商户id
     * Date: 2017-07-21
     * @param $adminId
     * @return array
     */
    public function getBrandIdShopIdFromAdminId($adminId)
    {
        $shopId = $this->getShopIdFromAdminId($adminId);
        $brandId = db('shop')->where(['id' => $shopId])->value('brandId');
        return ['shopId' => $shopId, 'brandId' => $brandId];
    }

    /**
     * 根据商户id查询校区列表
     * Date: 2017-07-24
     * @param $shopId
     * @return bool|false|\PDOStatement|string|\think\Collection
     */
    public function schoolListFromShopId($shopId)
    {
        if (!is_numeric($shopId)) {
            return false;
        }

        $where['shopId'] = $shopId;
        $where['status'] = ['exp', 'in(1,2)'];
        $data = db('school')->field('id,name')->where($where)->select();
        return $data;
    }

    /**
     * Date: 2017-10-24
     * 更新课程数
     * @param $classSchoolId
     * @return bool
     */
    public function updateSchoolCourseNum($classSchoolId)
    {
        $info = db('class_school')->field('brandId,shopId,schoolId')->where(['id' => $classSchoolId])->find();
        if (!$info) {
            return false;
        }

        Db::startTrans();
        try {
            db('shop')->where(['id' => $info['shopId']])->setInc('classNum');
            db('brand')->where(['id' => $info['brandId']])->setInc('classNum');
        } catch (Exception $e) {
            Db::rollback();
            return false;
        }
        operateLog('审核课程通过增加品牌和商户课程数', 'brand-shop', 0, 0);
        Db::commit();
        return true;
    }

}