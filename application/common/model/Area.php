<?php
/**
 * User: LiuTong
 * Date: 2017-07-18
 * Time: 9:16
 */

namespace app\common\model;

use think\Model;

class Area extends Model
{

    /**
     * 所有省、直辖市
     * Date: 2017-07-17
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function province()
    {
        $data = db('district')
            ->field('id,district_code,district_name')
            ->where(['district_level' => 0])
            ->select();
        return $data;
    }

    /**
     * 省的市或直辖市的区
     * Date: 2017-07-17
     * @param $provinceId
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function city($provinceId = null)
    {
        if (empty($provinceId)) {
            return false;
        }
        if (!is_numeric($provinceId)) {
            return false;
        }

        $data = db('district')
            ->field('id,district_code,district_name')
            ->where(['district_province_id' => $provinceId, 'district_level' => 1])
            ->select();
        return $data;
    }

    /**
     * 市的区或县
     * Date: 2017-07-17
     * @param $cityId
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function county($cityId = null)
    {
        if (empty($cityId)) {
            return false;
        }
        if (!is_numeric($cityId)) {
            return false;
        }

        $data = db('district')
            ->field('id,district_code,district_name')
            ->where(['district_city_id' => $cityId, 'district_level' => 2])
            ->select();
        return $data;
    }

    /**
     * 已开通城市
     * Date: 2017-07-19
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function cityOpen()
    {
        $data = db('exist_city')->field('cityCode,cityName')->where(['status' => 1])->select();
        return $data;
    }
}