<?php
/**
 * User: LiuTong
 * Date: 2017-07-20
 * Time: 15:06
 */

namespace app\common\model;

use think\Model;

class CourseCommon extends Model
{

    /**
     * 一级分类
     * Date: 2017-07-20
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function CourseTypesLevel1()
    {
        $data = db('class_type')->field('id,name,createTime')->where(['typeId' => 0, 'isDelete' => 0])->select();
        return $data;
    }

    /**
     * 二级分类
     * Date: 2017-07-20
     * @param $level1Id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function CourseTypesLevel2($level1Id)
    {
        $data = db('class_type')->field('id,name,typeId')->where(['typeId' => $level1Id, 'isDelete' => 0])->select();
        return $data;
    }

    /**
     * 根据二级分类查询一级分类
     * @param $level2Id
     * @return mixed
     */
    public function courseTypeLevel1FromLevel2($level2Id)
    {
        $level1Id = db('class_type')->where(['id' => $level2Id])->value('typeId');
        return $level1Id;
    }

    /**
     * 查询所有二级分类
     * Date: 2017-07-25
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function allCourseLevelTwo()
    {
        $data = db('class_type')->where(['level' => 2, 'isDelete' => 0])->select();
        return $data;
    }
}