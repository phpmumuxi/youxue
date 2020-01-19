<?php
namespace app\school\model;

use \think\Model;

class Finance extends Model
{
	//获取该校区的未删除的受益人
	public function getBenefits($schoolId){
		$res = db('user_benefit')
				-> field('id,name,userId')
			    -> where(['schoolId'=>$schoolId,'isDelete'=>0])
	            -> select();
	    return $res;
	}

	//获取该校区的未删除的顾问
	public function getAdvisers($schoolId){		  
	    $info = db('adviser_name')	      		
	           -> field('id,name,userId')
	           -> where('schoolId',$schoolId)
	           -> where('isDelete',0)	                   
	           -> select();
	    return $info;
	}

	//判断顾问是否被修改
	public function adviserIsUpdate($adviserId){		  
	    $time = db('adviser_name')      		
	           -> where('id',$adviserId)	           	                   
	           -> value('updateTime');
	    if($time>10000){
	    	return $time;
	    }
	    return false;
	}

	//获取校区统计数据 
    public function schoolStatistics($arr)
    {
      if($arr['type'] == 1){//按天查询
          $dataType = "FROM_UNIXTIME(recordDate,'%Y-%m-%d')";
      }else{//按月查询
          $dataType = "FROM_UNIXTIME(recordDate,'%Y-%m')";
      }
      $all  =  db('shop_money_record')  //总收入
              -> field($dataType.' as time,SUM(money) as totalMoney')
              -> where(['schoolId'=>$arr['schoolId']])
              ->where('recordDate','between',[$arr['start'],$arr['end']])
              -> group($dataType)
              -> select();
      $yes  =  db('shop_money_record')  //已入账
              -> field($dataType.' as time,SUM(money) as totalMoney')
              -> where(['isOff'=>1,'schoolId'=>$arr['schoolId']])
              ->where('recordDate','between',[$arr['start'],$arr['end']])
              -> group($dataType) 
              -> select();
      $no  =  db('shop_money_record') //未入账
              -> field($dataType.' as time,SUM(money) as totalMoney')
              -> where(['isOff'=>0,'schoolId'=>$arr['schoolId']])
              ->where('recordDate','between',[$arr['start'],$arr['end']])
              -> group($dataType) 
              -> select(); 
      $res = $this->getDoDatas([
            'all'=>$all,
            'yes'=>$yes,
            'no' =>$no
        ],$arr);
      return $res;      
    }

	//顾问收入统计
	public function adviserStatistics($arr){
		if($arr['type'] == 1){//按天查询
	        $dataType = "FROM_UNIXTIME(createTime,'%Y-%m-%d')";
	    }else{//按月查询
	        $dataType = "FROM_UNIXTIME(createTime,'%Y-%m')";
	    }
	    if($arr['adviserId']){
	    	$updateTime = $this->adviserIsUpdate($arr['adviserId']);
      		if($updateTime){
      			$where['createTime']=['>=',$updateTime];
      		}
	    	$userId = db('adviser_name')
				   -> where('isDelete=0')
				   -> where('id',$arr['adviserId'])
		           -> value('userId');
	    	$where['userId']=$userId;
	    }else{
	    	$userIds = $this->getAdvisers($arr['schoolId']);
	    	$userId = array_column($userIds,'userId');
	    	$userId =implode(',', $userId);
	    	$where['userId']=['in',$userId];
	    }
	    $where['type']=9;
		$data =db('record_money')
              -> field($dataType.' as time,SUM(money) as totalMoney')
              -> where($where)
              ->where('createTime','between',[$arr['start'],$arr['end']])
              -> group($dataType) 
              -> select();
        $res = $this->getDoDatas(['data'=>$data],$arr);
      	return $res;
	}

	//受益人收入统计
	public function benefitStatistics($arr){
		if($arr['type'] == 1){//按天查询
	        $dataType = "FROM_UNIXTIME(createTime,'%Y-%m-%d')";
	    }else{//按月查询
	        $dataType = "FROM_UNIXTIME(createTime,'%Y-%m')";
	    }
	    if($arr['benefitId']){
	    	$userId = db('user_benefit')
				   -> where('isDelete=0')
				   -> where('id',$arr['benefitId'])
		           -> value('userId');
	    	$where['userId']=$userId;
	    }else{
	    	$userIds = $this->getBenefits($arr['schoolId']);
	    	$userId = array_column($userIds,'userId');
	    	$userId =implode(',', $userId);
	    	$where['userId']=['in',$userId];
	    }
	    $where['type']=14;
		$data =db('record_money')
              -> field($dataType.' as time,SUM(money) as totalMoney')
              -> where($where)
              ->where('createTime','between',[$arr['start'],$arr['end']])
              -> group($dataType) 
              -> select();
        $res = $this->getDoDatas(['data'=>$data],$arr);
      	return $res;
	}

  //顾问销售额统计 
    public function adviserMoneyStatistics($arr)
    {                
        if($arr['type'] == 1){//按天查询           
            $dataType = "FROM_UNIXTIME(createTime,'%Y-%m-%d')";
        }else{//按月查询            
            $dataType = "FROM_UNIXTIME(createTime,'%Y-%m')";
        }

        if($arr['adviserId']){
            $where['adviserInitId'] =  $arr['adviserId'];
        }else{            
            $where['schoolId'] = $arr['schoolId'];
        }

      $allMoney=db('adviser_order')
                ->field($dataType.' as time,SUM(money) as totalMoney')
                ->where('orderType','in','3,7')
                ->where(['status'=>3])
                ->where($where)
                ->where('createTime','between',[$arr['start'],$arr['end']])
                ->group($dataType)
                ->select();
      $newMoney=db('adviser_order')
                ->field($dataType.' as time,SUM(money) as totalMoney')
                ->where('orderType','in','3,7')
                ->where(['status'=>3,'isOldCustom'=>2])
                ->where($where)
                ->where('createTime','between',[$arr['start'],$arr['end']])
                ->group($dataType)
                ->select();
      $oldMoney=db('adviser_order')
                ->field($dataType.' as time,SUM(money) as totalMoney')
                ->where('orderType','in','3,7')
                ->where(['status'=>3,'isOldCustom'=>1])
                ->where($where)
                ->where('createTime','between',[$arr['start'],$arr['end']])
                ->group($dataType)
                ->select();
      
      $res = $this->getDoDatas([
            'all'=>$allMoney,
            'new'=>$newMoney,
            'old' =>$oldMoney
        ],$arr);
      return $res;      
    }
    
	//柱状图数据处理
	private function getDoDatas($rr,$opt){
        $rs = [];
        $start = $opt['start'];
        $end = $opt['end'];
        $dd = [];
        $i=0;
        for(;$start<=$end;){
            
            if($opt['type'] == 1){
                $key = date('Y-m-d',$start);
                $start = strtotime('+1 day', $start);
            }else{
                $key = date('Y-m',$start);
                $start = strtotime('+1 month', $start);
            }
            $dd[$key] = 0;
            $rs['key'][$i++] = $key;
        }

        foreach($rr as $k=>$r){
            foreach($dd as $key=>$va)
            {
                if($r){
                  foreach($r as $k1=>$v){
                    if($v['time'] == $key){
                        $rs[$k][$key] = ($v['totalMoney']/1000);
                        unset($r[$k1]);
                        break;
                    }else{
                        $rs[$k][$key] = 0;
                    }
                  }
                }else{
                    $rs[$k][$key] = 0;
                }
            }
          $rs[$k] = array_values($rs[$k]);
        }
        return $rs;
    }




}