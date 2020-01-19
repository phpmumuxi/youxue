<?php
/**
 * User: LiuTong
 * Date: 2017-08-22
 * Time: 14:15
 */

namespace app\admin\model;

use think\Model;

class Vip extends Model
{

    /**
     * 会员等级列表
     * Date: 2107-08-22
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function vipList()
    {
        $ret = db('user_member')
            ->field('id,title,img,money,month,createTime,level,mMoney,isUse,prerogative')
            ->where(['isDelete' => 0])
            ->order('level asc')
            ->select();
        $data = $this->vipRightsSelect();
        foreach ($ret as $k => $v) {
            $p = explode(',', $v['prerogative']);
            $s = '';
            foreach ($data as $ke => $va) {
                if (in_array($va['id'], $p)) {
                    $s .= $va['name'].'、';
                }
            }
            $ret[$k]['s'] = mb_substr($s,0,-1);
        }
        return $ret;
    }

    /**
     * 等级为一的是否可以领取vip免费领商品
     * Date: 2017-08-22
     * @return bool
     */
    public function vipLevelOne()
    {
        $ret = db('user_member')
            ->where(['level' => 1, 'isDelete' => 0])
            ->value('isFree');
        if ($ret == 1) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 更新会员一级是否可以使用 会员免费领商品活动
     * Date: 2017-08-22
     * @param $isFree
     * @return int|string
     */
    public function changeLevelOneFree($isFree)
    {
        $ret = db('user_member')
            ->where(['level' => 1, 'isDelete' => 0])
            ->update(['isFree' => $isFree, 'modifyTime' => time()]);
        return $ret;
    }

    /**
     * 更改等级使用状态
     * Date: 2017-08-22
     * @param $id
     * @param $status
     * @return int|string
     */
    public function changeLevelUseStatus($id, $status)
    {
        $ret = db('user_member')
            ->where(['id' => $id])
            ->update(['isUse' => $status, 'modifyTime' => time()]);
        return $ret;
    }

    /**
     * 删除等级
     * Date: 2017-08-22
     * @param $id
     * @return int|string
     */
    public function deleteVipLevel($id)
    {
        $ret = db('user_member')
            ->where(['id' => $id])
            ->update(['isDelete' => 1]);
        return $ret;
    }

    /**
     * 保存vip
     * Date: 2017-08-23
     * @param $data
     * @return int|string
     */
    public function saveVip($data)
    {
        $data['createTime'] = time();
        $data['isUse'] = 0;
        $data['isFree'] = 1;
        $data['isDelete'] = 0;
        $ret = db('user_member')->insert($data);
        return $ret;
    }

    /**
     * 等级信息
     * Date: 2017-08-23
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     */
    public function levelInfo($id)
    {
        $data = db('user_member')
            ->field('id,title,img,money,mMoney,month,level,prerogative')
            ->where(['id' => $id])
            ->find();
        return $data;
    }

    /**
     * vip编辑保存
     * Date: 2017-08-23
     * @param $data
     * @param $id
     * @return int|string
     */
    public function saveEditVip($data, $id)
    {
        $data['modifyTime'] = time();
        $ret = db('user_member')
            ->where(['id' => $id])
            ->update($data);
        return $ret;
    }

    /**
     * 会员VIP购买订单
     * Date: 2017-09-12
     * @param string $orderNo
     * @param int $phone
     * @param array $sort
     * @param int $orderStatus
     * @return \think\Paginator
     */
    public function getVipBuyList($orderNo = '', $phone = 0, $sort = [], $orderStatus = -1)
    {
        $where = [];
        if ($orderNo) {
            $where['om.orderNo'] = $orderNo;
        }
        if ($phone) {
            $where['u.phone'] = $phone;
        }

        if ($sort) {
            $sort = 'om.' . $sort[0] . ' ' . $sort[1];
        }
        if ($orderStatus != -1) {
            $where['om.status'] = $orderStatus;
        }
        $data = db('order_member om')
            ->field('om.*,u.name as uName,u.phone')
            ->join('user u', 'u.id=om.userId', 'LEFT')
            ->where($where)
            ->order($sort)
            ->paginate(10);

        return $data;
    }

    /**
     * Date: 2017-09-20
     * 会员权限说明
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getVipRights()
    {
        $where['isDelete'] = 0;
        $data = db('user_member_ico')->where($where)->select();
        return $data;
    }

    /**
     * Date: 2017-09-20
     * 权益新增
     * @param $data
     * @return mixed
     */
    public function saveVipRights($data)
    {
        $data['createTime'] = time();
        $data['isDelete'] = 0;
        $ret = db('user_member_ico')->insert($data);
        return $ret;
    }

    /**
     * Date: 2017-09-20
     * 权益编辑
     * @param $data
     * @param $id
     * @return mixed
     */
    public function saveVipRightsEdit($data, $id)
    {
        $data['createTime'] = time();
        $ret = db('user_member_ico')->where(['id' => $id])->update($data, $id);
        return $ret;
    }

    /**
     * Date: 2017-09-20
     * 权益删除
     * @param $id
     * @return int|string
     */
    public function deleteVipRights($id)
    {
        $ret = db('user_member_ico')->where(['id' => $id])->update(['isDelete' => 1]);
        return $ret;
    }

    /**
     * Date: 2017-09-20
     * 权益详情
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     */
    public function getVipRightsDetail($id)
    {
        $data = db('user_member_ico')->where(['id' => $id])->find();
        return $data;
    }

    /**
     * Date: 2017-09-20
     * 权益选择
     * @return mixed
     */
    public function vipRightsSelect()
    {
        $data = db('user_member_ico')->field('id,name,text')->where(['isDelete' => 0])->select();
        return $data;
    }

}