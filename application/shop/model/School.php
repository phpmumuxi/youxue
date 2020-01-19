<?php
/**
 * User: LiuTong
 * Date: 2017-07-17
 * Time: 11:06
 */

namespace app\shop\model;

use function Couchbase\fastlzCompress;
use think\Db;
use think\Exception;
use think\Model;

class School extends Model
{

    /**
     * 查询商户所属校区
     * Date: 2017-07-17
     * @param $adminId
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getShopSchools($adminId)
    {
        $shopId = $this->getShopIdFromAdminId($adminId);

        $where['shopId'] = $shopId;
        $where['status'] = ['in', '1,2'];
        $data = db('school')
            ->alias('s')
            ->field('s.id,s.name as schoolName,s.address,s.longitude,s.latitude,s.phone,s.logo,s.userName,s.createTime,s.status')
            ->where($where)
            ->paginate(10, false);
//            ->select();

        /*
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $data[$k]['createTime'] = date('Y-m-d H:i:s', $v['createTime']);
                $data[$k]['statusName'] = ($v['status'] == 1) ? '上架中' : '已下架';
            }
        }
        */
        return $data;
    }

    /**
     * 根据管理员id查询其管理的商户id
     * Date: 2017-07-18
     * @param $adminId
     * @return mixed
     */
    private function getShopIdFromAdminId($adminId)
    {
        $shopId = db('admin')->where(['id' => $adminId])->value('shopId');
        return $shopId;
    }

    /**
     * 删除校区
     * Date: 2017-07-17、2017-07-18
     * @param $schoolId
     * @return bool
     */
    public function delSchool($schoolId)
    {
        // 1、删除该校区下所有课程(t_class_school) isDelete=1 schoolId
        // 2、删除该校区下所有课程返现规则(t_class_rule) isDelete=1 classSchoolId
        // 3、删除该校区(t_school)
        // 4、减去商户表(t_shop) schoolNum和classNum
        // 5、减去品牌表(t_brand) schoolNum和classNum

        // 课程数
        $classSchoolIds = Db::name('class_school')->field('id')->where(['schoolId' => $schoolId, 'isDelete' => 0])->select();
        $classNum = count($classSchoolIds);

        $classSchoolIdsTmp = [];
        foreach ($classSchoolIds as $k => $v) {
            array_push($classSchoolIdsTmp, $v['id']);
        }
        $classSchoolIds = implode(',', $classSchoolIdsTmp);

        $BrandShop = $this->getShopIdAndBrandIdFromSchoolId($schoolId);
        $shopId = $BrandShop['shopId'];
        $brandId = $BrandShop['brandId'];

        $goodBrandInfo = db('brand_home')->field('id,likeNum')->where(['brandId' => $brandId])->find();

        Db::startTrans();
        try {

            // 课程
            Db::name('class_school')->where(['schoolId' => $schoolId])->update(['isDelete' => 1]);
            // 返现规则
            $where['classSchoolId'] = ['in', $classSchoolIds];
            Db::name('class_rule')->where($where)->update(['isDelete' => 1]);
            // 校区
            Db::name('school')->where(['id' => $schoolId])->update(['status' => 3]);

            // 防止空操作即删除空品牌或空商户 Date: 2017-07-25
            if ($classNum != 0) {
                $update['schoolNum'] = ['exp', 'schoolNum-1'];
            }
            $update['classNum'] = ['exp', 'classNum-' . $classNum];
            // 商户
            Db::name('shop')->where(['id' => $shopId])->update($update);
            // 品牌
            Db::name('brand')->where(['id' => $brandId])->update($update);

            if ($goodBrandInfo) {
                db('brand_home')->where(['id' => $goodBrandInfo['id']])->delete();
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }
        return true;

    }

    /**
     * 查询校区所属的商户id和品牌id
     * @param $schoolId
     * @return array|false|\PDOStatement|string|Model
     */
    private function getShopIdAndBrandIdFromSchoolId($schoolId)
    {
        $data = db('school')->field('shopId,brandId')->where(['id' => $schoolId])->find();
        return $data;
    }

    /**
     * 校区详细信息
     * Date: 2017-07-17
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     */
    public function schoolDetail($id)
    {
        $where = ['id' => $id];
        $data = db('school')
            ->alias('s')
            ->field('s.id,s.name as schoolName,s.address,s.longitude,s.latitude,s.phone,s.logo,s.userName,s.createTime,s.status,s.sort,s.cityCode,s.cityName,s.logo,s.img,s.intr')
            ->where($where)
            ->find();
        return $data;
    }

    /**
     * 更新校区信息
     * Date: 2017-07-19
     * @param $data
     * @param $id
     * @return int|string
     */
    public function saveEditSchool($data, $id)
    {
        $ret = db('school')->where(['id' => $id])->update($data);
        return $ret;
    }

    /**
     * 新增校区
     * Date: 2017-07-19
     * @param $data
     * @param $adminId
     * @return bool
     */
    public function saveAddSchool($data, $adminId)
    {
        $shopId = $this->getShopIdFromAdminId($adminId);
        $data['shopId'] = $shopId;

        $brandId = $this->getBrandIdFromShopId($shopId);
        $data['brandId'] = $brandId;

        $data['createTime'] = time();
        $data['sort'] = 100;// 默认排序
        //$data['status'] = 2;// 下架
        $data['status'] = 1;// 改为默认上架

        Db::startTrans();
        try {
            DB::name('school')->insert($data);

            DB::name('shop')->where(['id' => $shopId])->setInc('schoolNum');
            DB::name('brand')->where(['id' => $brandId])->setInc('schoolNum');

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }
        return true;

    }

    /**
     * 根据商户id查询商户代理的品牌
     * Date: 2017-07-19
     * @param $shopId
     * @return mixed
     */
    private function getBrandIdFromShopId($shopId)
    {
        $brandId = db('shop')->where(['id' => $shopId])->value('brandId');
        return $brandId;
    }

    /**
     * 检查校区是否有课程
     * Date: 2017-07-20
     * @param $schoolId
     * @return bool
     */
    public function checkSchoolIfHaveClass($schoolId)
    {
        $ret = db('class_school')->where(['schoolId' => $schoolId, 'isDelete' => 0, 'status' => 2])->count();
        if ($ret > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 更新校区上、下架状态
     * Date: 2017-07-20
     * @param $schoolId
     * @param $status 1上架 2下架 3删除
     * @return bool
     */
    public function updateSchoolStatus($schoolId, $status)
    {
        $ret = db('school')->where(['id' => $schoolId])->update(['status' => $status]);
        if ($ret) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Date: 2017-11-03
     * 商户下架校区
     * @param $schoolId
     * @param $adminId
     * @return bool
     */
    public function downSchool($schoolId, $adminId)
    {
        // 下架校区 下架校区的课程 删除课程审核规则 减商户课程数 品牌课程数
        $schoolInfo = db('school')->where(['id' => $schoolId])->find();
        if (empty($schoolInfo)) {
            return false;
        }

        $shopId = $schoolInfo['shopId'];
        $brandId = $schoolInfo['brandId'];

        // 查询当前品牌是否属于优选品牌
        $goodBrandInfo = db('brand_home')->field('id,likeNum')->where(['brandId' => $brandId])->find();

        // 商户校区数
        $shopInfo = db('shop')->field('schoolNum,classNum')->where(['id' => $shopId])->find();
        // 品牌课程数
        $brandInfo = db('brand')->field('schoolNum,classNum')->where(['id' => $brandId])->find();

        // 课程数 上架状态
        $classSchoolIdsArray = Db::name('class_school')->field('id')->where(['schoolId' => $schoolId, 'status' => 2])->select();
        $classNum = count($classSchoolIdsArray);

        $classSchoolIds = '';
        if ($classNum > 0) {
            $classSchoolIdsTmp = [];
            foreach ($classSchoolIdsArray as $k => $v) {
                array_push($classSchoolIdsTmp, $v['id']);
            }
            $classSchoolIds = implode(',', $classSchoolIdsTmp);
        }

        try {
            // 课程下架
            Db::name('class_school')->where(['schoolId' => $schoolId])->update(['status' => 3]);
            operateLog('校区课程下架', 'class_school schoolId = ' . $schoolId, 0, $adminId);
            // 返现规则 删除
            if ($classSchoolIds) {
                $where['classSchoolId'] = ['in', $classSchoolIds];
                Db::name('class_rule')->where($where)->update(['isDelete' => 1]);
                operateLog('校区课程审核规则删除 classSchoolIds = ' . $classSchoolIds, 'class_rule', 0, $adminId);
            }
            // 校区
            Db::name('school')->where(['id' => $schoolId])->update(['status' => 2]);
            operateLog('校区下架', 'school', $schoolId, $adminId);

            // 防止溢减
            $updateShop['schoolNum'] = 0;
            if ($shopInfo['schoolNum'] > 0) {
                $updateShop['schoolNum'] = ['exp', 'schoolNum-1'];
            }
            $updateShop['classNum'] = 0;
            if ($shopInfo['classNum'] > $classNum) {
                $updateShop['classNum'] = ['exp', 'classNum-' . $classNum];
            }

            $updateBrand['schoolNum'] = 0;
            if ($brandInfo['schoolNum'] > 0) {
                $updateBrand['schoolNum'] = ['exp', 'schoolNum-1'];
            }
            $updateBrand['classNum'] = 0;
            if ($brandInfo['classNum'] > $classNum) {
                $updateBrand['classNum'] = ['exp', 'classNum-' . $classNum];
            }

            // 当品牌课程数为0时 删除该品牌所属的优选品牌
            if ($updateBrand['classNum'] == 0 && !empty($goodBrandInfo)) {
                db('brand_home')->where(['id' => $goodBrandInfo['id']])->delete();
                operateLog('品牌课程为0时,(实际)删除该优选品牌 likeNum = ' . $goodBrandInfo['likeNum'], 'brand_home', 0, $adminId);
            }

            // 商户
            Db::name('shop')->where(['id' => $shopId])->update($updateShop);
            operateLog('商户减 校区以及校区课程数 courseNum = ' . $classNum, 'shop', 0, $adminId);
            // 品牌
            Db::name('brand')->where(['id' => $brandId])->update($updateBrand);
            operateLog('品牌减 校区以及校区课程数 courseNum = ' . $classNum, 'brand', 0, $adminId);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Date: 2017-10-20
     * 校区上架
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function upSchool($id, $adminId)
    {
        $schoolInfo = db('school')->where(['id' => $id])->find();
        if (empty($schoolInfo)) {
            return false;
        }
        try {
            db('school')->where(['id' => $id])->update(['status' => 1]);
            operateLog('校区上架', 'school', $id, $adminId);
            db('shop')->where(['id' => $schoolInfo['shopId']])->setInc('schoolNum');
            operateLog('商户校区数加1', 'shop', $id, $adminId);
            db('brand')->where(['id' => $schoolInfo['brandId']])->setInc('schoolNum');
            operateLog('品牌校区数加1', 'brand', $id, $adminId);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Date: 2017-11-08
     * 删除校区并删除校区课程
     * @param $schoolId
     * @param $adminId
     * @return bool
     */
    public function delSchoolNew($schoolId, $adminId)
    {
        Db::startTrans();
        try {
            // 删除校区
            db('school')->where(['id' => $schoolId])->update(['status' => 3]);
            operateLog('删除校区', 'school', $schoolId, $adminId);
            // 删除校区课程
            db('class_school')->where(['schoolId' => $schoolId])->update(['isDelete' => 1]);
            operateLog('删除校区课程', 'class_school', 0, $adminId);
        } catch (Exception $e) {
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

}
