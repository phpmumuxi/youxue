<?php
namespace app\admin\model;

use \think\Model;
use \think\Db;

class RecordMoney extends Model
{
  //商户收入列表
  public function getShopsMoneysFinance($shopName)
    {
      $where = $shopName ? ['name' => ['like', "%$shopName%"]] : '';
      $data = db('shop')
            ->field('id,name,money as yuEMoney')
            ->where('status!=3')
            ->where($where)
            ->paginate(10, false, ['query' => ['shopName' => $shopName]]);
      return $data;
    }

    //商户收入金额数据处理
    public function doShopMoneyDatas($data)
    {
        $shopsMoney = db('record_shop') 
                    ->field('shopId,SUM(money) as tiXianMoney')
                    ->where('type=2')
                    ->group('shopId')
                    ->select();
        foreach($data['data'] as $k => $v){
            $data['data'][$k]['tiXianMoney'] = 0;
            if($shopsMoney){
                foreach($shopsMoney as $ke => $va){
                    if($v['id'] == $va['shopId']){
                        $data['data'][$k]['tiXianMoney'] = $va['tiXianMoney'];
                    }
                }
            }
        }
        return $data;
    }


  //顾问列表
	public function getAdviserLists($data)
    {
        $where['a.isDelete']='0';
        if($data['phone']){
          $where['a.phone']=$data['phone'];
        }
        if($data['shopId']){
          $where['a.shopId']=$data['shopId'];
        }
        if($data['schoolId']){
          $where['a.schoolId']=$data['schoolId'];
        }

        $data = db('adviser_name')   //所有顾问
              ->alias('a')
              ->field('a.id,a.name,a.phone,s.name as shopName,a.updateTime,a.userId,sc.name as schoolName')
              ->where($where)
              ->join('t_shop s','a.shopId=s.id','LEFT')
              ->join('t_school sc','a.schoolId=sc.id','LEFT')
              ->group('a.id')
              ->order('a.id desc')
              ->paginate(10, false, ['query' => ['phone' => $data['phone'],'shopId' => $data['shopId'],'schoolId' => $data['schoolId']]]);    
        return $data;
    }

    //顾问总收入、数据处理
    public function doDataMoney($data)
    {
        $adviserMoney = db('record_money') 
                      ->field('userId,SUM(money) as allMoney')
                      ->where('type=9')
                      ->group('userId')
                      ->select();
        foreach($data['data'] as $k => $v){
            $data['data'][$k]['allMoney'] = 0;
            if($adviserMoney){
                foreach($adviserMoney as $ke => $va){
                    if($v['userId'] == $va['userId']){
                        $data['data'][$k]['allMoney'] = $va['allMoney'];
                    }
                }
            }
        }
        return $data;
    }

     //校区数据列表
    public function getSchoolLists($shopId='')
    {
        $where=$shopId?['shopId'=>$shopId]:'';
        $data = db('school') 
              ->field('id,name')
              ->where($where)
              ->where('status!=3')
              ->select();
        return $data;
    }

    //商户数据列表
  public function getShopLists()
    {
        $data = db('shop')
              ->field('id,name')
              ->where('status!=3')
              ->select();     
        return $data;
    }

    //某顾问实际收入（被替换）
    public function getAdviserNewMoney($arr)
    {
      $moneys = db('record_money') 
              ->field('SUM(money) as allMoney')
              ->where(['type'=>9,'userId'=>$arr['userId']])
              ->where('createTime','>=',$arr['updateTime'])
              ->select();
      return $moneys['0']['allMoney']?$moneys['0']['allMoney']:0;
    }

    //获取某商户所有校区收入详情
    public function getShopBills($id)
    {
        $data = db('school')
              ->alias('sc') 
              ->field('sc.id as schoolId,sc.name as schoolName,s.id as shopId,s.name as shopName')
              ->where('sc.status!=3') //删除的校区
              ->where(['sc.shopId'=>$id])
              ->join('t_shop s','sc.shopId=s.id','LEFT')
              ->paginate(10);
        return $data;
    }

    //某商户所有校区收入数据处理
    public function doShopBillDatas($data,$shopId)
    {
        $moneys = db('shop_money_record') 
                ->field('shopId,schoolId,isOff,SUM(money) as allMoney')
                ->where('shopId='.$shopId)
                ->group('schoolId,isOff')
                ->select();                   
        foreach($data['data'] as $k => $v){
            $data['data'][$k]['onMoney'] = 0;//已出账
            $data['data'][$k]['offMoney'] = 0;//未出账
            if($moneys){
                foreach($moneys as $ke => $va){
                    if($v['schoolId'] == $va['schoolId']){
                        if($va['isOff'] == 1){
                          $data['data'][$k]['onMoney'] = $va['allMoney'];
                        }else{
                          $data['data'][$k]['offMoney'] = $va['allMoney'];
                        }
                    }
                }
            }
        }
        return $data;
    }

    //顾问列表
  public function getAdviserMoneyLists($date)
    {
        if($date['shopId']){
          $where['a.shopId']=$date['shopId'];
        }
        if($date['schoolId']){
          $where['a.schoolId']=$date['schoolId'];
        }
        $where['a.isDelete']=0;
        $data = db('adviser_name a')
              ->field('a.id,a.name,a.phone,s.name as shopName,sc.name as schoolName')
              ->where($where)
              ->join('t_shop s','a.shopId=s.id','LEFT')           
              ->join('t_school sc','a.schoolId=sc.id','LEFT')           
              ->paginate(10,false,['query' => ['shopId'=>$date['shopId'],'schoolId'=>$date['schoolId'],'sTime'=>$date['sTime'],'eTime'=>$date['eTime']]]);                 
        return $data;
    }

    //顾问销售额列表
    public function getAdviserMoneyDatas($data,$date)
    {
      $start=strtotime($date['sTime']);
      $end=strtotime($date['eTime'])+86399;
       
      $allMoney=db('adviser_order')
                ->field("adviserInitId,SUM(money) as allMoney")
                ->where('orderType','in',[3,7])
                ->where(['status'=>3])
                ->where('createTime','between',[$start,$end])
                ->group('adviserInitId')
                ->select();
      $newMoney=db('adviser_order')
                ->field("adviserInitId,SUM(money) as newMoney")
                ->where('orderType','in',[3,7])
                ->where(['status'=>3,'isOldCustom'=>2])
                ->where('createTime','between',[$start,$end])
                ->group('adviserInitId')
                ->select();
      $oldMoney=db('adviser_order')
                ->field("adviserInitId,SUM(money) as oldMoney")
                ->where('orderType','in',[3,7])
                ->where(['status'=>3,'isOldCustom'=>1])
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
        if($arr['status']){
          $where['o.isOldCustom']=$arr['status'];
        }
        $where['o.isSign']=1;
        $where['o.adviserId']=$arr['id'];
        $where['o.signDate']=['between',[$start,$end]];
        $res= db('order_class o')
            ->field('o.orderNo,o.name,o.money,o.signDate,o.isOldCustom,u.name as userName,u.phone as userPhone')
            ->where($where)
            ->join('t_user u','u.id = o.userId','LEFT')
            ->paginate(10,false,['query' => ['status'=>$arr['status']],'var_page' => 'page']);
        
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
  
  //顾问销售额导出
  public function adviserMoneyDatas($arr)
  {
        if($arr['shopId']){
          $where['a.shopId']=$arr['shopId'];
        }
        if($arr['schoolId']){
          $where['a.schoolId']=$arr['schoolId'];
        }
        $where['a.isDelete']=0;
        $data = db('adviser_name a')
              ->field('a.id,a.name,a.phone,s.name as shopName,sc.name as schoolName')
              ->where($where)
              ->join('t_shop s','a.shopId=s.id','LEFT')           
              ->join('t_school sc','a.schoolId=sc.id','LEFT')           
              ->select();

        $start=strtotime($arr['sTime']);
        $end=strtotime($arr['eTime'])+86399;

        $allMoney=db('adviser_order')
                ->field("adviserInitId,SUM(money) as allMoney")
                ->where('orderType','in',[3,7])
                ->where(['status'=>3])
                ->where('createTime','between',[$start,$end])
                ->group('adviserInitId')
                ->select();
        $newMoney=db('adviser_order')
                ->field("adviserInitId,SUM(money) as newMoney")
                ->where('orderType','in',[3,7])
                ->where(['status'=>3,'isOldCustom'=>2])
                ->where('createTime','between',[$start,$end])
                ->group('adviserInitId')
                ->select();
        $oldMoney=db('adviser_order')
                ->field("adviserInitId,SUM(money) as oldMoney")
                ->where('orderType','in',[3,7])
                ->where(['status'=>3,'isOldCustom'=>1])
                ->where('createTime','between',[$start,$end])
                ->group('adviserInitId')
                ->select();          
      
        foreach($data as $k => $v){
            $data[$k]['allMoney'] = 0;
            $data[$k]['newMoney'] = 0;
            $data[$k]['oldMoney'] = 0;
           
            if($allMoney){
                foreach($allMoney as $k1 => $v1){
                    if($v['id'] == $v1['adviserInitId']){
                        $data[$k]['allMoney'] = $v1['allMoney'];
                        unset($allMoney[$k1]);
                    }
                }
            }
            if($newMoney){
                foreach($newMoney as $k2 => $v2){
                    if($v['id'] == $v2['adviserInitId']){
                        $data[$k]['newMoney'] = $v2['newMoney'];
                        unset($newMoney[$k2]);
                    }
                }
            }
            if($oldMoney){
                foreach($oldMoney as $ko => $vo){
                    if($v['id'] == $vo['adviserInitId']){
                        $data[$k]['oldMoney'] = $vo['oldMoney'];
                        unset($oldMoney[$ko]);
                    }
                }
            }
        }
        // halt($data);
      return $data; 
  }
  
  //平台总收入情况
  public function getAdminTotalMoney($arr)
  {
      $start=strtotime($arr['sTime']);
      $end=strtotime($arr['eTime'])+86399;
      $where['recordDate']=['between',[$start,$end]];
      $data=[];
      $all  =  db('shop_money_record')
            -> where($where)
            -> sum('money');
      $yes  =  db('shop_money_record')
            -> where('isOff=1')
            -> where($where)
            -> sum('money');
      $no   =  db('shop_money_record')
            -> where('isOff=0')
            -> where($where)
            -> sum('money');            
      $data['all'] = $all?$all:0;     
      $data['yes'] = $yes?$yes:0; 
      $data['no'] = $no?$no:0; 
      return $data;       
  }

  //平台总收入情况
  public function getShopsMoneysList($data)
  {
      $where = $data['shopId'] ? ['id' => $data['shopId']] : '';
      $data = db('shop')
            ->field('id,name,money as yuEMoney')
            ->where('status!=3')
            ->where($where)
            ->paginate(10, false, ['query' => ['sTime' => $data['sTime'],'eTime' => $data['eTime'],'shopId' => $data['shopId']]]);
      return $data;
  }

  public function doShopMoneyList($data,$arr)
  {
      $start=strtotime($arr['sTime']);
      $end=strtotime($arr['eTime'])+86399;
      $where['recordDate']=['between',[$start,$end]];

      $allMoney = db('shop_money_record') 
                  ->field('shopId,SUM(money) as allMoney')
                  ->where($where)
                  ->group('shopId')
                  ->select();
      $yesMoney = db('shop_money_record') 
                  ->field('shopId,SUM(money) as yesMoney')
                  ->where($where)
                  ->where('isOff=1')
                  ->group('shopId')
                  ->select();
      $noMoney = db('shop_money_record') 
                  ->field('shopId,SUM(money) as noMoney')
                  ->where($where)
                  ->where('isOff=0')
                  ->group('shopId')
                  ->select();
      foreach($data['data'] as $k => $v){
            $data['data'][$k]['allMoney'] = 0;
            $data['data'][$k]['yesMoney'] = 0;
            $data['data'][$k]['noMoney'] = 0;
            if($allMoney){
                foreach($allMoney as $k1 => $v1){
                    if($v['id'] == $v1['shopId']){
                        $data['data'][$k]['allMoney'] = $v1['allMoney'];
                        unset($allMoney[$k1]);
                    }
                }
            }
            if($yesMoney){
                foreach($yesMoney as $k2 => $v2){
                    if($v['id'] == $v2['shopId']){
                        $data['data'][$k]['yesMoney'] = $v2['yesMoney'];
                        unset($yesMoney[$k2]);
                    }
                }
            }
            if($noMoney){
                foreach($noMoney as $k3 => $v3){
                    if($v['id'] == $v3['shopId']){
                        $data['data'][$k]['noMoney'] = $v3['noMoney'];
                        unset($noMoney[$k3]);
                    }
                }
            }
      }
      return $data;
  }

  //获取某商户所有校区
  public function getShopSchoolList($shopId,$schoolId)
  {
      if($schoolId){
        $where['id']=$schoolId;
      }
      $where['shopId'] =$shopId;
      $data = db('school')
            ->field('id,name')
            ->where('status!=3') 
            ->where($where)
            ->paginate(10,false, ['query' => ['schoolId' => $schoolId]]);
      return $data;
  }

  //某商户所有校区收入数据处理
  public function doAdminMoneyShopBill($data,$arr)
  {
      $start=strtotime($arr['sTime']);
      $end=strtotime($arr['eTime'])+86399;
      $where['recordDate']=['between',[$start,$end]];
      $where['shopId']=$arr['id'];
      $moneys = db('shop_money_record') 
              ->field('shopId,schoolId,isOff,SUM(money) as allMoney')
              ->where($where)
              ->group('schoolId,isOff')
              ->select();                   
      foreach($data['data'] as $k => $v){
          $data['data'][$k]['onMoney'] = 0;//已出账
          $data['data'][$k]['offMoney'] = 0;//未出账
          if($moneys){
              foreach($moneys as $ke => $va){
                  if($v['id'] == $va['schoolId']){
                      if($va['isOff'] == 1){
                        $data['data'][$k]['onMoney'] = $va['allMoney'];
                      }else{
                        $data['data'][$k]['offMoney'] = $va['allMoney'];
                      }
                  }
              }
          }
      }
      return $data;
  }

   /*该校区账单详情    
    *$type 1为优选课程2万人砍课程
    */
  public function getAdminMoneyDetailBill($arr,$type){
    $start=strtotime($arr['sTime']);
    $end=strtotime($arr['eTime'])+86399;
    if($type==1){//优选课程
        if($arr['status']){
          $where['o.isOldCustom']=$arr['status'];
        }
        $where['o.isSign']=1;
        $where['o.schoolId']=$arr['id'];
        $where['o.signDate']=['between',[$start,$end]];
        $res= db('order_class o')
            ->field('o.orderNo,o.name,o.money,o.shopMoney,o.signDate,o.isOldCustom,u.name as userName,u.phone as userPhone')
            ->where($where)
            ->join('t_user u','u.id = o.userId','LEFT')
            ->paginate(10,false,['query' => ['status'=>$arr['status']],'var_page' => 'page']);
        
    }else{ //万人砍课程  
        $res= db('activity_wrk_info w')
            -> field('o.orderNo,o.name as activityName,o.money,w.name as className,w.signTime,u.name as userName,u.phone as userPhone')   
            ->where(['w.isSign' => 1,'w.schoolId' => $arr['id']])
            ->where('w.signTime','between',[$start,$end])           
            ->join('t_activity_wrk_order o','o.id = w.wrkOrderId','LEFT')
            ->join('t_user u','u.id = w.userId','LEFT')
            ->paginate(10,false,['query' => [],'var_page' => '_page']);
    }
      return $res;
  }

}