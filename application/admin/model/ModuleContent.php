<?php
/**
 * User: LiuTong
 * Date: 2017-09-29
 * Time: 14:29
 */

namespace app\admin\model;

use think\Db;
use think\Exception;
use think\Model;

class ModuleContent extends Model
{
    /**
     * Date: 2017-09-29
     * 模块信息列表
     * @return \think\Paginator
     */
    public function getModuleContentList()
    {
        $where['isDelete'] = 0;
        $data = db('informat')
            ->field('id,type,createTime,name')
            ->where($where)
            ->order('type')
            ->paginate(10);
        return $data;
    }

    /**
     * Date: 2017-09-29
     * 新增模块说明
     * @param $data
     * @return bool
     */
    public function addModuleContent($data)
    {
        $data['createTime'] = time();
        $data['isDelete'] = 0;
        $ret = db('informat')->insert($data);
        return $ret ? true : false;
    }

    /**
     * Date: 2017-09-29
     * 获取某个模块信息
     * @param $id
     * @return mixed
     */
    public function getModuleContentData($id)
    {
        $data = db('informat')->field('id,content,type')->where(['id' => $id])->find();
        return $data;
    }

    /**
     * Date: 2017-09-29
     * 修改模块说明
     * @param $id
     * @param $data
     * @param $adminId
     * @return bool
     */
    public function updateModuleContent($id, $data, $adminId)
    {
        Db::startTrans();
        try {
            db('informat')->where(['id' => $id])->update(['isDelete' => 1]);
            $data['createTime'] = time();
            $data['isDelete'] = 0;
            db('informat')->insert($data);
            operateLog('修改模块说明', 'informat', $id, $adminId);
        } catch (Exception $e) {
            dump($e->getMessage());
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

    /**
     * Date: 2017-09-29
     * 删除模块说明
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function deleteModuleContent($id, $adminId)
    {
        $ret = db('informat')->where(['id' => $id])->update(['isDelete' => 1]);
        operateLog('删除模块说明', 'informat', $id, $adminId);
        return $ret ? true : false;
    }

    /**
     * Date: 2017-10-10
     * 获取已存在类型
     * @return mixed
     */
    public function getModuleType()
    {
        $where['isDelete'] = 0;
        $data = db('informat')->field('type')->where($where)->select();
        $types = [];
        if ($data) {
            foreach ($data as $k => $v) {
                array_push($types, $v['type']);
            }
        }
        return $types;
    }
}