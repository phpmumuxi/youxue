<?php
namespace app\shop\model;

use \think\Model;

class ShopMoneyRecord extends Model
{
	//商户账单列表
	public function getShopBills($shopId,$data){
    $end=strtotime($data['endTime'])+86399;
		$where =  "mr.recordDate between ".strtotime($data['startTime'])." AND ".$end;
		$res= db('shop_money_record')
			->alias('mr')
			-> field('mr.id,mr.status,(mr.recordDate + s.time*86400) as outTime,mr.recordDate as inTime,mr.money,mr.isOff,s.name as shopName,s.bankUserName,s.bankName,s.bankCard')
			->join('t_shop s','s.id = mr.shopId','LEFT')			
			->where('s.status!=3')
			->where('s.id',$shopId)
			->where($where)
			->order('mr.recordDate DESC')
			->paginate(10, false, ['query' => ['startTime' => $data['startTime'],'endTime' => $data['endTime']]]);
		return $res;
	}

	//商户总收入
	public function getShopTotalMoney($shopId,$data){
    $end=strtotime($data['endTime'])+86399;
		$where = "recordDate between ".strtotime($data['startTime'])." AND ".$end;
		$money=  db('shop_money_record')
				->where('isOff=1')
				->where('shopId',$shopId)
				->where($where)
				->sum('money');
		return $money?$money:0;
	}

	//该商户账单详情
	public function getBillClassDetail($id,$type){
		$re =  db('shop_money_record')
  			->field('recordDate,shopId')
  			->find($id);
		$startTime = strtotime(date('Y-m-d',$re['recordDate']));//签约日期
		$endTime = $startTime + 86399;	//一天24小时

    if($type==1){
    		$res= db('order_class o')
      			->field('o.orderNo,o.name,o.money,o.shopMoney,o.signDate,u.name as userName,u.phone as userPhone')			
      			->where('o.isSign = 1')
      			->where('o.shopId',$re['shopId'])
      			->where('o.signDate','between',[$startTime,$endTime])
            ->join('t_user u','u.id = o.userId','LEFT')
      			->paginate(10,false,['query' => [],'var_page' => 'page']);
    }else{    
        $res= db('activity_wrk_info w')
            -> field('o.orderNo,o.name as activityName,w.name as className,w.shopMoney,w.signTime,u.name as userName,u.phone as userPhone')     
            ->where('w.isSign = 1')
            ->where('w.shopId',$re['shopId'])
            ->where('w.signTime','between',[$startTime,$endTime])
            ->join('t_activity_wrk_order o','o.id = w.wrkOrderId','LEFT')
            ->join('t_user u','u.id = w.userId','LEFT')
            ->paginate(10,false,['query' => [],'var_page' => '_page']);
    }
      return $res;
	}

	//该商户下所有校区统计数据 
    public function shopStatistics($arr)
    {
	    if($arr['type'] == 1){//按天查询
          $dataType = "FROM_UNIXTIME(recordDate,'%Y-%m-%d')";
      }else{//按月查询
          $dataType = "FROM_UNIXTIME(recordDate,'%Y-%m')";
      }
      $where = $arr['schoolId'] ? ['schoolId' => $arr['schoolId']] : '';

      $all  =  db('shop_money_record')
              -> field($dataType.' as time,SUM(money) as totalMoney')
              -> where('shopId='.$arr['shopId'])
              -> where($where)
              ->where('recordDate','between',[$arr['start'],$arr['end']])
              -> group($dataType)
              -> select();
	  
      $yes  =  db('shop_money_record')
              -> field($dataType.' as time,SUM(money) as totalMoney')
              -> where(['isOff'=>1,'shopId'=>$arr['shopId']])//已出账
              -> where($where)
              ->where('recordDate','between',[$arr['start'],$arr['end']])
              -> group($dataType) 
              -> select();
      $no  =  db('shop_money_record')
              -> field($dataType.' as time,SUM(money) as totalMoney')
              -> where(['isOff'=>0,'shopId'=>$arr['shopId']])//未出账
              -> where($where)
              ->where('recordDate','between',[$arr['start'],$arr['end']])
              -> group($dataType) 
              -> select(); 
      $res = $this->getDoDatas([
            'all'=>$all,
            'yes'=>$yes,
            'no' =>$no
        ],$arr,1);
      return $res;      
    }

    //获取顾问统计数据 
    public function adviserStatistics($arr)
    {
      if($arr['type'] == 1){//按天查询
          $dataType = "FROM_UNIXTIME(createTime,'%Y-%m-%d')";
      }else{//按月查询
          $dataType = "FROM_UNIXTIME(createTime,'%Y-%m')";
      }

      $where1 = $arr['schoolId'] ? ['schoolId' => $arr['schoolId']] : '';
      $userIds = db('adviser_name')
              -> field('userId')
              -> where($where1)
              -> where(['shopId'=>$arr['shopId'],'isDelete'=>0])
              -> select();
      $userId = array_column($userIds,'userId');
      $userId =implode(',', $userId);
      if($userId){        
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

    //获取受益人统计数据 
    public function benefitStatistics($arr)
    {
      if($arr['type'] == 1){//按天查询
          $dataType = "FROM_UNIXTIME(createTime,'%Y-%m-%d')";
      }else{//按月查询
          $dataType = "FROM_UNIXTIME(createTime,'%Y-%m')";
      }
      
      $where1=$arr['schoolId']?'schoolId='.$arr['schoolId']:'';
      $userIds = db('user_benefit')
              -> field('userId')
              -> where($where1)
              -> where(['shopId'=>$arr['shopId'],'isDelete'=>0])
              -> select();
      $userId = array_column($userIds,'userId');
      $userId =implode(',', $userId);
      if($userId){        
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

    //该商户下所有校区
	public function getShopHasSchools($shopId){
		$data=  db('school')
				->field('id,name')
				->where('status!=3')
				->where('shopId',$shopId)
				->select();
		return $data;
	}

  //顾问销售额统计 
    public function adviserMoneyStatistics($arr)
    {                
        if($arr['type'] == 1){//按天查询           
            $dataType = "FROM_UNIXTIME(createTime,'%Y-%m-%d')";
        }else{//按月查询            
            $dataType = "FROM_UNIXTIME(createTime,'%Y-%m')";
        }
        if($arr['schoolId']){
            $where['schoolId'] =  $arr['schoolId'];
        }else{
            $shopIds=$this->getShopHasSchools($arr['shopId']);
            $schoolIds = implode(',',array_column($shopIds,'id'));
            $where['schoolId'] = ['in',$schoolIds];
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
    
	private function getDoDatas($rr,$opt,$type=''){
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
                        if($type){
                            $rs[$k][$key] = ($v['totalMoney']*0.994)/1000;
                        }else{
                            $rs[$k][$key] = ($v['totalMoney']/1000);
                        }                       
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

     //顾问列表(商户下所有校区的顾问)
  public function getAdviserMoneyLists($shopId,$schoolId,$date)
    {
        $where= $schoolId?['a.schoolId'=>$schoolId]:'';
        $data = db('adviser_name a')
              ->field('a.id,a.name,a.phone,s.name as schoolName')
              ->where(['a.shopId'=>$shopId,'a.isDelete'=>0])            
              ->where($where)
              ->join('t_school s','s.id = a.schoolId','LEFT')           
              ->paginate(10,false,['query' => ['schoolId'=>$schoolId,'sTime'=>$date['sTime'],'eTime'=>$date['eTime']]]);                 
        return $data;
    }

    //顾问销售额列表
    public function getAdviserMoneyDatas($data,$date,$shopId)
    {
      $start=strtotime($date['sTime']);
      $end=strtotime($date['eTime'])+86399;

      $shopIds=$this->getShopHasSchools($shopId);
      $schoolIds = implode(',',array_column($shopIds,'id'));

      $allMoney=db('adviser_order')
                ->field("adviserInitId,SUM(money) as allMoney")
                ->where('orderType','in',[3,7])
                ->where(['status'=>3])
                ->where('schoolId','in',$schoolIds)
                ->where('createTime','between',[$start,$end])
                ->group('adviserInitId')
                ->select();
      $newMoney=db('adviser_order')
                ->field("adviserInitId,SUM(money) as newMoney")
                ->where('orderType','in',[3,7])
                ->where(['status'=>3,'isOldCustom'=>2])
                ->where('schoolId','in',$schoolIds)
                ->where('createTime','between',[$start,$end])
                ->group('adviserInitId')
                ->select();
      $oldMoney=db('adviser_order')
                ->field("adviserInitId,SUM(money) as oldMoney")
                ->where('orderType','in',[3,7])
                ->where(['status'=>3,'isOldCustom'=>1])
                ->where('schoolId','in',$schoolIds)
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