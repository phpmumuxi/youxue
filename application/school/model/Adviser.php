<?php
namespace app\school\model;

use \think\Model;
use \think\Db;

class Adviser extends Model
{
	//校区后台顾问列表
	public function getAdviserDatas($schoolId,$phone){
		if($phone){
            $where = 'phone='.$phone;
        }else{
        	$where = '';
        }
		  
	    $info = db('adviser_name')	      		
	           -> field('id,name,phone,createTime')
	           -> where('schoolId',$schoolId)
	           -> where('isDelete',0)
	           -> where($where)	           
	           -> paginate(10, false, ['query' => [
                'phone' => $phone]]);
	    return $info;
	}

	//判断顾问是否存在或者注册为用户或已经是受益人
	public function hasAdviserOrUser($arr,$id){
		//是否已经是受益人
		$re = $this->adviserIsBenefit($arr['phone']);
	    if($re){ //手机号唯一，不能同时存在 在两个校区内
	    	return 'isBenefit';
	    }
		$info = $this->adviserIsHas($arr['phone']);
	    if($info){ //手机号唯一，不能同时存在 在两个校区内
	    	return 'has';
	    }
	    $userId =$this->adviserIsUser($arr['phone']);
	            
		if($userId){
			return $this->insertAdviser($arr,$id,$userId);
		}else{
			$userNewId= $this->insertAdviserInUser($arr);
			return $this->insertAdviser($arr,$id,$userNewId);
		}
	}

	//添加一条顾问数据
	public function insertAdviser($arr,$id,$userId){
		$data  = db('admin')
	           -> field('shopId,schoolId')
			   -> find($id);
		$arr['createTime']=time();		
		$arr['updateTime']=0;		
		$arr['userId']=$userId;
		$arr['shopId']=$data['shopId'];
		$arr['schoolId']=$data['schoolId'];
		$arr['isDelete']=0;
		
		Db::startTrans();
        try {        	
			db('adviser_name')->insert($arr);
			db('user')->where('id',$userId)->setField('isAdviser',1);
			Db::commit();
		} catch (\Exception $e) {
            Db::rollback();
            return 'err';
        }		
		return 'ok';		
	}

	//该顾问不是用户，先增添一条
	public function insertAdviserInUser($arr){
		$data['phone'] = $arr['phone'];
		$data['createTime'] = 0;
		$data['isAdviser'] = 1;
		$data['token'] = '0';
		$data['name'] = $arr['name'];
		$data['memberLevel'] = 0;
		$data['status'] = 0;
		$data['isCmbc'] = 0;
		return db('user')->insertGetId($data);
	}

	//编辑顾问信息
	public function editAdviserInfos($arr,$adminId){
		if($arr['type']){//手机号变化
			$userId =$this->adviserIsUser($arr['phone']);
			if(!$userId) { return 'no';}
			//是否已经是受益人
			$res = $this->adviserIsBenefit($arr['phone']);
		    if($res){ //手机号唯一，不能同时存在 在两个校区内
		    	return 'isBenefit';
		    }
			$info = $this->adviserIsHas($arr['phone']);
		    if($info){ //手机号唯一，不能同时存在 在两个校区内
		    	return 'has';
		    }
			//根据手机号判断  是改手机号还是改顾问
			$oldUserId = db('adviser_name')-> where('id',$arr['id'])
	           -> value('userId');
	        unset($arr['type']);
			if($userId==$oldUserId){ //userId相同，只是改手机号，不是改顾问		
				Db::startTrans();
		        try {
           			db('adviser_name')-> update($arr);
           			db('user')->where('id',$userId)->setField('isAdviser',1);
					Db::commit();
				} catch (\Exception $e) {
		            Db::rollback();
		            return 'err';
		        }		
				return 'changePhone';	                    
			}else{//改顾问，新增一条数据（原顾问下客户可以重新分配）
				unset($arr['id']);						
				return $this->insertAdviser($arr,$adminId,$userId);	
			}
		}else{//只改名字或者不修改	
			unset($arr['type']);
			if(db('adviser_name')->update($arr) !== false){
				return 'ok';
			}else{
				return 'err';
			}
		}
	}

	//判断该顾问下是否有用户
	public function adviserHasUser($id){
		$info = db('adviser_user_school')
			   -> where('isDelete=0')
			   -> where('adviserId',$id)
	           -> value('id');
	    if($info){
	    	return 'has';
		}else{
			//user表中 是否是顾问 状态改变(状态不改变在其他校区收入可以看)
			// $userId=db('adviser_name')->field('userId')->find($id);
			// $res=db('user')->where('id',$userId['userId'])->setField('isAdviser',0);
			// if(db('adviser_name')->delete($id) && $res){
			if(db('adviser_name')->update(['id'=>$id,'isDelete'=>1])){
				return 'ok';
			}else{				
				return 'err';
			}
		}
	}


	//该顾问下所有用户
	public function adviserDownUsers($id,$phone){
		$where = $phone ? "u.phone=" .$phone : '';
		$info = db('adviser_order')
			   -> alias('a')
			   -> field('a.userId,a.adviserId,a.schoolId,u.name as userName,u.phone')
			   -> where('a.adviserId',$id)			   
			   -> where($where)			   
			   -> join('user u','u.id=a.userId','LEFT')
			   -> group('a.userId')
	           ->paginate(10, false, ['query' => [
                'id' => $id,'phone' => $phone]]);
	    return $info;
	}

	//根据手机号查找用户
	public function adviserIsUser($phone){
		$userId = db('user')
			   -> where('phone',$phone)
	           -> value('id');
	    return $userId;
	}

	//根据手机号判断 受益人是否存在
	public function adviserIsBenefit($phone){
		$info = db('user_benefit')
			   -> where('phone',$phone)
			   -> where('isDelete',0)
	           -> value('id');
	 	return $info;
	}

	//根据手机号判断 顾问是否存在
	public function adviserIsHas($phone){
		$info = db('adviser_name')
			   -> where('phone',$phone)
			   -> where('isDelete',0)
	           -> value('id');
	 	return $info;
	}

	//获取该校区的未删除的顾问
	public function getAdvisers($schoolId){		  
	    $info = db('adviser_name')	      		
	           -> field('id,name')
	           -> where('schoolId',$schoolId)
	           -> where('isDelete',0)	                   
	           -> select();
	    return $info;
	}

	//重新分配顾问
	public function distributeAdviser($ids,$adviserId,$schoolId,$remark,$adminId,$oldAdviserId){
      	$logs=[];
      	$logs['content']=$remark;
      	$logs['oldAdviserId']=$oldAdviserId;
      	$logs['adviserId']=$adviserId;      	
      	$logs['adminId']=$adminId;
      	$logs['schoolId']=$schoolId;
      	$logs['createTime']=time();
		$datas=[];		
		$userIds=explode(',', $ids);      
      	foreach($userIds as $key=>$vo){	        
	        $datas[$key]['userId'] = $vo;
	        $datas[$key]['adviserId'] = $adviserId;
	        $datas[$key]['schoolId'] = $schoolId;
	        $datas[$key]['createTime'] = time();
	        $datas[$key]['isDelete'] = 0;	        
      	}
      	Db::startTrans();
        try {
        	//记录操作日志
        	db('adviser_log')->insert($logs);
	      	//改状态
			db('adviser_user_school')
		    ->where('userId','in',$ids)
		    ->where(['schoolId'=>$schoolId,'isDelete' =>0])
		    ->update(['isDelete' => 1 ]);
		    //批量插入
	      	db('adviser_user_school')->insertAll($datas);
	      	//订单顾问改变
	      	db('adviser_order')
		    ->where('userId','in',$ids)
		    ->where(['schoolId'=>$schoolId])
		    ->update(['adviserId' => $adviserId,'isShift' => 1 ]);
		    //顾问用户关系变动
	      	db('adviser_user')
		    ->where('adviserId',$oldAdviserId)		    
		    ->update(['adviserId' => $adviserId]);
      		Db::commit();
		} catch (\Exception $e) {
            Db::rollback();
            return false;
        }
        	return true;		
	}

	// 重新分配顾问日志     
    public function getAdviserLogs($schoolId,$adviserId)
    {
    	$where = $adviserId ? "l.oldAdviserId=" .$adviserId : '';
    	$res= db('adviser_log')
    		-> alias('l')
    		-> field('l.content,l.createTime,a.name as oldAdviser,an.name as newAdviser,n.name as doAdmin')
    		->where('l.schoolId',$schoolId)
    		->where($where)
    		-> join('adviser_name a','a.id=l.oldAdviserId','LEFT')
    		-> join('adviser_name an','an.id=l.adviserId','LEFT')
    		-> join('admin n','n.id=l.adminId','LEFT')
    		-> order('createTime DESC')
			->paginate(10, false, ['query' => ['adviserId' => $adviserId,'schoolId' => $schoolId]]);
    	return $res;
    }

    //顾问列表(分页)
  public function getAdviserMoneyLists($schoolId,$date)
    {
        $data = db('adviser_name')
              ->field('id,name,phone')
              -> where('schoolId',$schoolId)
              ->where('isDelete',0)            
              ->paginate(10,false,['query' => ['sTime'=>$date['sTime'],'eTime'=>$date['eTime']]]);                 
        return $data;
    }

    //顾问销售额列表
    public function getAdviserMoneyDatas($data,$date,$schoolId)
    {
      $start=strtotime($date['sTime']);
      $end=strtotime($date['eTime'])+86399;
       
      $allMoney=db('adviser_order')
                ->field("adviserInitId,SUM(money) as allMoney")
                ->where('orderType','in',[3,7])
                ->where(['status'=>3,'schoolId'=>$schoolId])
                ->where('createTime','between',[$start,$end])
                ->group('adviserInitId')
                ->select();
      $newMoney=db('adviser_order')
                ->field("adviserInitId,SUM(money) as newMoney")
                ->where('orderType','in',[3,7])
                ->where(['status'=>3,'isOldCustom'=>2,'schoolId'=>$schoolId])
                ->where('createTime','between',[$start,$end])
                ->group('adviserInitId')
                ->select();
      $oldMoney=db('adviser_order')
                ->field("adviserInitId,SUM(money) as oldMoney")
                ->where('orderType','in',[3,7])
                ->where(['status'=>3,'isOldCustom'=>1,'schoolId'=>$schoolId])
                ->where('createTime','between',[$start,$end])
                ->group('adviserInitId')
                ->select();          
      
        foreach($data['data'] as $k => $v){
            $data['data'][$k]['allMoney'] = 0;
            $data['data'][$k]['newMoney'] = 0;
            $data['data'][$k]['oldMoney'] = 0;
            $data['data'][$k]['sTime'] = $date['sTime'];
            $data['data'][$k]['eTime'] = $date['eTime'];
            if($allMoney){
                foreach($allMoney as $k1 => $v1){
                    if($v['id'] == $v1['adviserInitId']){
                        $data['data'][$k]['allMoney'] = $v1['allMoney'];
                        unset($allMoney[$k1]);
                    }
                }
            }
            if($newMoney){
                foreach($newMoney as $k2 => $v2){
                    if($v['id'] == $v2['adviserInitId']){
                        $data['data'][$k]['newMoney'] = $v2['newMoney'];
                        unset($newMoney[$k2]);
                    }
                }
            }
            if($oldMoney){
                foreach($oldMoney as $ko => $vo){
                    if($v['id'] == $vo['adviserInitId']){
                        $data['data'][$k]['oldMoney'] = $vo['oldMoney'];
                        unset($oldMoney[$ko]);
                    }
                }
            }
        }
        // halt($data);
      return $data;
    }

    /*该商户账单详情    
    *$type 1为优选课程2万人砍课程
    */
  public function getBillMoneyDetail($arr,$type){
    $start=strtotime($arr['sTime']);
    $end=strtotime($arr['eTime'])+86399;
    if($type==1){//优选课程
        $res= db('order_class o')
            ->field('o.orderNo,o.name,o.money,o.signDate,o.isOldCustom,u.name as userName,u.phone as userPhone')
            ->where(['o.isSign'=>1,'o.adviserId'=>$arr['id']])
            ->where('o.signDate','between',[$start,$end])
            ->join('t_user u','u.id = o.userId','LEFT')
            ->paginate(10,false,['query' => [],'var_page' => 'page']);
        
    }else{ //万人砍课程  
        $res= db('activity_wrk_info w')
            -> field('o.orderNo,o.name as activityName,o.money,w.signTime,u.name as userName,u.phone as userPhone')   
            ->where(['w.isSign' => 1,'w.adviserId' => $arr['id']])
            ->where('w.signTime','between',[$start,$end])           
            ->join('t_activity_wrk_order o','o.id = w.wrkOrderId','LEFT')
            ->join('t_user u','u.id = w.userId','LEFT')
            ->group('w.wrkOrderId')
            ->paginate(10,false,['query' => [],'var_page' => '_page']);
    }
      return $res;
  }

}