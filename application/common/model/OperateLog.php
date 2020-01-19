<?php
/**
 * User: LiuTong
 * Date: 2017-09-20
 * Time: 15:39
 */

namespace app\common\model;

use think\Model;

class OperateLog extends Model
{
    public function __construct($content, $table, $id, $adminId)
    {
        $this->insertOperateLog($content, $table, $id, $adminId);
    }

    /**
     * Date: 2017-09-20
     * 增加操作记录
     * @param $content
     * @param $table
     * @param $id
     * @param $adminId
     */
    public function insertOperateLog($content, $table, $id, $adminId)
    {
        $data['content'] = $content;
        $data['table'] = $table;
        $data['fromId'] = $id;
        $data['adminId'] = $adminId;
        $data['createTime'] = time();
        db('admin_operate_log')->insert($data);
    }

}