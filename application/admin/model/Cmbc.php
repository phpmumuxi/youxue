<?php
namespace app\admin\model;

use \think\Model;

class Cmbc extends Model
{
    //招商码列表数据
	public function getUserCmbcs($phone,$status){
        if ($status != -1) {
            $where['c.isUse'] = $status;
        } else {
            $where['c.isUse'] = ['exp', '>=0'];
        }

		$phone?($where['u.phone']=$phone):'';
        $where['c.isDelete'] = 0;

		$lists= db('cmbc_code c')
                ->field('c.id,c.cmbcCode,c.createTime,c.isUse,c.useTime,c.term,u.name as userName,u.phone')
				->where($where)
                ->join('user u', 'u.id=c.userId', 'LEFT')
				->paginate(10, false, ['query' => ['phone' => $phone,'status' => $status]]);
		return $lists;
	}

	//生成招商码
	public function addUserCmbcCode($term,$data){
        $arr=[];
		foreach ($data as $k => $v) {
            $arr[$k]['cmbcCode']=$v;
            $arr[$k]['createTime']=time();
            $arr[$k]['isUse']=0;
            $arr[$k]['term']=$term;
            $arr[$k]['isDelete']=0;
        }
        if(db('cmbc_code')->insertAll($arr)){
            return true;
        }
        return false;
	}

}