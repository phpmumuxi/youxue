<?php
namespace app\admin\model;

use \think\Model;

class Statistics extends Model
{
  //获取地图上所有校区经纬度数据
	public function getSchoolMap($id=''){
     $where = $id ? ['shopId' => $id] : '';
		 $res = db('school')
	        ->field('id,name,longitude,latitude')
          ->where('status!=3')
          ->where($where)
	        ->select();
		return $res;
	}

	//获取未删除的商户
	public function getShops(){
		 $res = db('shop')
	 		    ->where('status!=3')
	        ->field('id,name')
	        ->select();
		return $res;
	}

  //获取未删除的顾问
  public function getSchoolAdviser($id){
     $res = db('adviser_name')
          ->where(['isDelete'=>0,'schoolId'=>$id])
          ->field('id,name')
          ->select();
    return $res;
  }

	//课程销售情况
	public function getCourseSales($arr){
		$start = strtotime($arr['startTime']);
    $end = strtotime($arr['endTime'])+86399;
    $where=$arr['shopId']?"o.shopId=".$arr['shopId']:'';
		$res = 	db('order_class')
				->alias('o')
				->field('o.name,o.money,s.name as shopName,count(*) as total')		
				->where('o.status=1 OR o.status=4')
				->where('o.signDate','between',[$start,$end])
				->where($where)
				->join('t_shop s','s.id=o.shopId','LEFT')
				->group('o.classSchoolId')
				->paginate(10, false, ['query' => [
               'shopId' => $arr['shopId'],'startTime' => $arr['startTime'],'endTime' => $arr['endTime']]]);
		return $res;
	}

	//不同品牌销售额
	public function brandStatistics($arr){
    $arr['start'] = strtotime($arr['aTime']);        
    if($arr['type'] == 1){//按天查询
        $arr['end'] = strtotime($arr['bTime'])+86399;        
    }else{//按月查询
        $t=$arr['bTime'].'-01';            
        $arr['end'] = strtotime("$t +1 month")-1;        
    }
    
		$brands = db('brand')
				->field('id,name')
				->where('isDelete=0')
				->order('sort DESC')
				->select();
		
    $moneys = db('shop_money_record r')
        -> field('s.brandId,SUM(r.money) as allMoney')            
        -> where('r.recordDate','between',[ $arr['start'],$arr['end']])
        -> where('s.status!=3')
        -> join('t_shop s','s.id=r.shopId','LEFT')        
        -> group('s.brandId')
        -> select();
                     
		foreach($brands as $key=>$va){
		  $brands[$key]['money'] = 0;
      if($moneys){
    			foreach($moneys as $k=>$v){
    				if($v['brandId'] == $va['id']){					
    					$brands[$key]['money'] = ($v['allMoney']/10000);
    					unset($moneys[$k]);
    					break;					
    				}
    			}
      }
		}
		$data=[];
		$data['key'] = array_column($brands, 'name');
		$data['money'] =array_column($brands, 'money');
		return $data;
	}

	 //销售额统计 
    public function moneyStatistics($arr)
    {
        $arr['start'] = strtotime($arr['aTime']);        
        if($arr['type'] == 1){//按天查询
            $arr['end'] = strtotime($arr['bTime'])+86399;
            $dataType = "FROM_UNIXTIME(recordDate,'%Y-%m-%d')";
        }else{//按月查询
            $t=$arr['bTime'].'-01';            
            $arr['end'] = strtotime("$t +1 month")-1;
            $dataType = "FROM_UNIXTIME(recordDate,'%Y-%m')";
        }
        
      if($arr['shopId'] > 0 && $arr['schoolId']==0){//商户
          $where['shopId'] =  $arr['shopId'];
      }elseif($arr['shopId'] > 0 && $arr['schoolId'] > 0){//校区
          $where['schoolId'] =  $arr['schoolId'];
      }else{//全部
          $where='';
      }

      $all  =  db('shop_money_record')
              -> field($dataType.' as time,SUM(price) as totalMoney')
              -> where($where)
              ->where('recordDate','between',[ $arr['start'],$arr['end']])
              -> group($dataType)
              -> select();
      
      $res = $this->getDoDatas(['all'=>$all],$arr);
      return $res;      
    }

    //顾问销售额统计 
    public function adviserStatistics($arr)
    {
        $arr['start'] = strtotime($arr['aTime']);        
        if($arr['type'] == 1){//按天查询
            $arr['end'] = strtotime($arr['bTime'])+86399;
            $dataType = "FROM_UNIXTIME(createTime,'%Y-%m-%d')";
        }else{//按月查询
            $t=$arr['bTime'].'-01';            
            $arr['end'] = strtotime("$t +1 month")-1;
            $dataType = "FROM_UNIXTIME(createTime,'%Y-%m')";
        }
        
      if($arr['shopId'] > 0 && $arr['schoolId']==0 && $arr['adviserId']==0){//商户
          $shopIds=$this->getSchoolMap($arr['shopId']);
          $schoolIds = implode(',',array_column($shopIds,'id'));
          $where['schoolId'] = ['in',$schoolIds];
      }elseif($arr['shopId'] > 0 && $arr['schoolId'] > 0 && $arr['adviserId']==0){//校区
          $where['schoolId'] =  $arr['schoolId'];
      }elseif($arr['shopId'] > 0 && $arr['schoolId'] > 0 && $arr['adviserId'] > 0){//顾问
          $where['adviserInitId'] =  $arr['adviserId'];
      }else{//全部
          $where='';
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
                        //$rs[$k][$key] = (int)$v['totalMoney'];//整形
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