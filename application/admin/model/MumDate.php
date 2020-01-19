<?php
/**
 * User: LiuTong
 * Date: 2017-09-04
 * Time: 11:31
 */

namespace app\admin\model;

use think\Model;

class MumDate extends Model
{
    /**
     * 获取妈妈约活动列表
     * Date: 2017-09-04
     * @param string $phone
     * @return mixed
     */
    public function getMumDateList($phone = '')
    {
        $where = [];
        if ($phone) {
            $where['md.phone'] = $phone;
        }
        $where['isDelete'] = 0;
        $data = db('mom_date md')
            ->field('md.id,md.name,u.name as uName,md.phone,md.address,md.dateTime,md.peopleNum,md.winNum,md.isSponsor,md.status')
            ->join('user u', 'u.id=md.userId', 'LEFT')
            ->where($where)
            ->paginate(10, false);
        return $data;
    }

    /**
     * 更新人数上限
     * Date: 2017-09-04
     * @param $id
     * @param $num
     * @return int|string
     */
    public function editPeopleNum($id, $num)
    {
        $ret = db('mom_date')->where(['id' => $id])->update(['peopleNum' => $num]);
        return $ret;
    }

    /**
     * 删除该活动
     * Date:  2017-09-04
     * @param $id
     * @param $note
     * @return int|string
     */
    public function delPeopleNum($id, $note)
    {
        $data['note'] = $note;
        $data['isDelete'] = 1;
        $ret = db('mom_date')->where(['id' => $id])->update($data);
        return $ret;
    }

    /**
     * 活动详情
     * Date: 2017-09-05
     * @param $id
     * @return mixed
     */
    public function getMumDateDetail($id)
    {
        $where['md.id'] = $id;
        $data = db('mom_date md')
            ->field('md.*,u.name as uName')
            ->join('user u', 'u.id=md.userId', 'LEFT')
            ->where($where)
            ->find();
        return $data;
    }

    /**
     * 七天开始和结束时间
     * Date: 2017-09-06
     * @return array
     */
    public function sevenDay()
    {
        $a = strtotime(date('Y-m-d'));
        $b = strtotime('-7 days', $a);
        return ['aTime' => $b, 'bTime' => $a];
    }

    /**
     * 赞助列表
     * Date: 2017-08-06
     * @param string $aTime
     * @param string $bTime
     * @return mixed
     */
    public function getMumDateSupport($aTime = '', $bTime = '')
    {
        if ($aTime && $bTime) {
//            $aTime = strtotime($aTime);
//            $bTime = strtotime($bTime);
            $where['mds.createTime'] = ['between', [$aTime, $bTime]];
        } else {
            $sevenDay = $this->sevenDay();
            $aTime = $sevenDay['aTime'];
            $bTime = $sevenDay['bTime'];
            $where['mds.createTime'] = ['between', [$aTime, $bTime]];
        }

        $where['mds.isDelete'] = 0;
        $data = db('mom_date_sponsor mds')
            ->field('mds.id,mds.momDateId,count(mds.id) as supportNum,md.winNum,md.name as eName,u.name as uName')
            ->join('mom_date md', 'md.id=mds.momDateId', 'LEFT')
            ->join('user u', 'u.id=md.userId', 'LEFT')
            ->where($where)
            ->group('mds.momDateId')
            ->order('supportNum DESC')
            ->paginate(10, false);

        return $data;
    }

}