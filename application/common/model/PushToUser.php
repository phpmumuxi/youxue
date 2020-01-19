<?php
/**
 * User: LiuTong
 * Date: 2017-10-19
 * Time: 9:11
 */

namespace app\common\model;

use think\Model;

class PushToUser extends Model
{
    /**
     * Date: 2017-10-19
     * 新增推送消息记录
     * @param $userId
     * @param $adminId
     * @param $title
     * @param $content
     * @param $type
     * @return bool
     */
    public function InsertPushUser($userId, $adminId, $title, $content, $type)
    {
        $data['createTime'] = time();
        $data['userId'] = $userId;
        $data['adminId'] = $adminId;
        $data['title'] = $title;
        $data['content'] = $content;
        $data['type'] = $type;
        $data['isRead'] = 0;
        $ret = db('push_user')->insert($data);
        return $ret ? true : false;
    }

}