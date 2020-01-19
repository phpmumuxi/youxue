<?php
/**
 * User: LiuTong
 * Date: 2017-09-29
 * Time: 10:33
 */

namespace app\admin\model;

use think\Model;

class SharePerson extends Model
{
    /**
     * Date: 2017-09-29
     * 分享人列表
     * @return mixed
     */
    public function getSharePersonList()
    {
        $where['u.shareId'] = ['>', 0];
        $data = db('user u')
            ->field('s.id,s.name,s.phone')
            ->join('user s', 's.id=u.shareId', 'LEFT')
            ->group('u.shareId')
            ->where($where)
            ->paginate(10);
        return $data;
    }

    /**
     * Date: 2017-09-29
     * 被分享人列表
     * @param $id
     * @return \think\Paginator
     */
    public function getSharePersonPersonList($id)
    {
        $data = db('user')
            ->field('id,name,phone')
            ->where(['shareId' => $id])
            ->paginate(10);
        return $data;
    }
}