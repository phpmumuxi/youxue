<?php
namespace app\admin\model;

use \think\Model;
// use \think\Db;

class Star extends Model
{
    //获取星星灯列表数据
	public function getStarLists($name=''){
		$where = $name ? ['name' => ['like', "%$name%"]] : '';
		$lists= db('star_rule')
				->where($where)
				->where(['isDelete'=>0])
				->paginate(10, false, ['query' => ['name' => $name]]);
		return $lists;
	}

	//免费券订单(第一次参加赠送)
	public function getfirstFreeOrders($status,$phone,$schoolId){
		if ($status != -1) {
            $where['sfu.status'] = $status;
        } else {
            $where['sfu.status'] = ['exp', '>=0'];
        }

        $phone?($where['u.phone']=$phone):'';
        $schoolId?($where['sfu.schoolId']=$schoolId):'';

		$data = db('star_free_use sfu')
            ->field('sfu.status,sfu.useTime,sfu.createTime,u.name as userName,u.phone,an.name as adviserName,s.name as schoolName')
            ->join('user u', 'u.id=sfu.userId', 'LEFT')
            ->join('adviser_name an', 'an.id=sfu.adviserId', 'LEFT')
            ->join('school s', 's.id=sfu.schoolId', 'LEFT')
            ->where($where)
            ->paginate(10, false, ['query' => ['status' => $status,'phone' => $phone,'schoolId' => $schoolId]]);
        return $data;
	}

	//星星兑换课程订单
	public function getStarCourseOrders($status,$phone,$schoolId){
		if ($status != -1) {
            $where['su.status'] = $status;
        } else {
            $where['su.status'] = ['exp', '>=0'];
        }

        $phone?($where['u.phone']=$phone):'';
        $schoolId?($where['su.schoolId']=$schoolId):'';

		$data = db('star_use su')
            ->field('su.starNum,su.classNum,su.className,su.status,su.useTime,su.createTime,u.name as userName,u.phone,an.name as adviserName,s.name as schoolName')
            ->join('user u', 'u.id=su.userId', 'LEFT')
            ->join('adviser_name an', 'an.id=su.adviserId', 'LEFT')
            ->join('school s', 's.id=su.schoolId', 'LEFT')
            ->where($where)
            ->paginate(10, false, ['query' => ['status' => $status,'phone' => $phone,'schoolId' => $schoolId]]);
        return $data;
	}

	//灯券兑换课程订单
	public function getStarVoucherOrders($status,$phone,$schoolId){
		if ($status != -1) {
            $where['stu.status'] = $status;
        } else {
            $where['stu.status'] = ['exp', '>=0'];
        }

        $phone?($where['u.phone']=$phone):'';
        $schoolId?($where['stu.schoolId']=$schoolId):'';

		$data = db('star_ticket_use stu')
            ->field('sr.name as starName,stu.classNum,stu.className,stu.status,stu.useTime,stu.createTime,u.name as userName,u.phone,s.name as schoolName')
            ->join('user u', 'u.id=stu.userId', 'LEFT')
            ->join('star_rule sr', 'sr.id=stu.ticketId', 'LEFT')
            ->join('school s', 's.id=stu.schoolId', 'LEFT')
            ->where($where)
            ->paginate(10, false, ['query' => ['status' => $status,'phone' => $phone,'schoolId' => $schoolId]]);
        return $data;
	}

	//获取校区（未删除）
	public function getSchools(){
		$data  = db('school')
	            ->field('id,name')            
	            ->where('status!=3')
	            ->select();
        return $data;
	}

    //用户活动管理
    public function getUserActivity($phone){
        $phone?($where['u.phone']=$phone):'';
        $where['sur.status']=0;
        $data = db('star_user_record sur')
            ->field('u.name as userName,u.phone as userPhone,su.shareTime,su.starNum,sur.userId,sur.starRound,sur.createTime')
            ->join('user u', 'u.id=sur.userId', 'LEFT')
            ->join('star_user su', 'su.userId=sur.userId', 'LEFT')
            ->where($where)
            ->order('sur.createTime desc')
            ->paginate(10, false, ['query' => ['phone' => $phone]]);
        return $data;
    }

    //上轮活动详情
    public function getUserLastActivity($userId,$round){
        $re = db('star_user_record')
            ->field('starNum,starStatus')
            ->where(['status'=>1,'userId'=>$userId,'starRound'=>$round])
            ->find();
        $data='';
        if($re){
            $data['starNum']=$re['starNum'];
            if($re['starStatus']==0){
                $data['status']='可使用';
            }else{
                $data['status']='已过期';
            }
        }
        return $data;
    }

    //用户活动详情
    public function getUserActivityInfo($userId){
        $data = db('star_rule_user sru')
            ->field('st.name as ticketName,st.num,st.remainNum,st.endTime,sru.name as lampName,sru.starNum,sru.status,sru.starRound,sru.isEnd')
            ->where('sru.userId',$userId)
            ->join('star_ticket st', 'st.starId=sru.id', 'LEFT')
            ->order('sru.starRound desc,sru.level asc')
            ->paginate(12);
        return $data;
    }

}