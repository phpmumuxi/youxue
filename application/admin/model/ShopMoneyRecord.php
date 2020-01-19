<?php
namespace app\admin\model;

use \think\Model;
use think\Db;
use app\admin\model\Shop as ShopModel;

class ShopMoneyRecord extends Model
{
	/**
     * 查询出账详细
     * 
     */
    public function ArrivalOutMoney($arr,$page)
    {
      $time = $arr['start_time'];
      $date = date('Y-m-d',$time);
      if($arr['type']==1){ //指定日期未处理的账单
        $where =  "FROM_UNIXTIME(mr.recordDate+(86400*s.time), '%Y-%m-%d') = '".$date."' and mr.status!=2";
      }else{ //所有历史未处理账单
        $where = "FROM_UNIXTIME(mr.recordDate+(86400*s.time), '%Y-%m-%d') < '".$date."' and mr.status!=2";
      }
      if($page){
        $page='_page';
      }else{
        $page='page';
      }
      $where1 = $arr['bankType'] ? ['s.bankType' => $arr['bankType']] : '';
      
      $lists  =  db('shop_money_record')
              -> alias('mr')
              -> field("s.id,s.name,s.time,s.bankName,FROM_UNIXTIME(mr.recordDate, '%Y-%m-%d') as inDate,FROM_UNIXTIME(mr.recordDate+(86400*s.time), '%Y-%m-%d') as outDate,SUM(mr.money) as money,mr.isOff,mr.status,mr.shopId")
              -> where('s.status !=3') //商户删除状态
              -> where($where1)
              -> join('t_shop s','s.id=mr.shopId','LEFT')
              -> where($where)
              ->group("mr.shopId,FROM_UNIXTIME(mr.recordDate, '%Y-%m-%d')")
              -> paginate(10, false, ['query' => [
               'start_time' => $date,'type' => $arr['type']],'var_page' => $page]);
      return $lists;
    }

    //查询出账详细
    public function ArrivalexportMoneys($type)
    {
      $date = date('Y-m-d',time());
      if($type==1){ //当日未处理的账单
        $where =  "FROM_UNIXTIME(mr.recordDate+(86400*s.time), '%Y-%m-%d') = '".$date."' and mr.status!=2";
      }else{ //所有历史未处理账单
        $where = "FROM_UNIXTIME(mr.recordDate+(86400*s.time), '%Y-%m-%d') < '".$date."' and mr.status!=2";
      }
      
      $lists  =  db('shop_money_record')
              -> alias('mr')
              -> field("s.id,s.name,s.time,s.bankUserName,
            s.bankCard,s.bankName,FROM_UNIXTIME(mr.recordDate, '%Y-%m-%d') as inDate,FROM_UNIXTIME(mr.recordDate+(86400*s.time), '%Y-%m-%d') as outDate,SUM(mr.money) as money,mr.recordDate, 
            mr.isOff,mr.status,mr.id as rid")
              -> where('s.status !=3') //商户删除状态
              -> join('t_shop s','s.id=mr.shopId','LEFT')
              -> where($where)
              ->group("mr.shopId,FROM_UNIXTIME(mr.recordDate, '%Y-%m-%d')")
              -> select();
      return $lists;
    }

    //获取各个商户的余额
    public function getShopMoney($ids)
    {
      $lists  =  db('shop')
              ->field('id,money')
              ->lock(true)
              ->where('id in('.$ids.')')
              ->select();
      return $lists;
    }

    //数据确认成功  各个表状态处理
    public function doDatasRecord($arr,$moneyArr,$ids)
    {
      Db::startTrans();//使用事务机制
      try {
        //插入商户金额记录
        db('record_shop')->insertAll($arr);

        //更新商户余额
        $shop_model = new ShopModel();      
        $shop_model->saveAll($moneyArr);            

        //更新商户收入记录表（出账、处理）状态
        db('shop_money_record')
          ->where('id in('.$ids.')')
          ->update([
              'isOff'=>1,
              'status'=>2,
              'enterTime'=>time()
            ]);       
        Db::commit();
      } catch (\Exception $e) {
        Db::rollback();
        return false;
      }
        return true;
    }

    //获取商户数据 (未删除的商户)
    public function getShops()
    {
      $lists  =  Db::name('shop')
              -> field('id,name')
              -> where('status!=3')
              -> select(); 
      return $lists;
    }

    //获取校区数据 (未删除的校区)
    public function getSchools()
    {
      $lists  =  Db::name('school')
              -> field('id,name')
              -> where('status!=3')
              -> select(); 
      return $lists;
    }

    //获取推荐人
    public function getReferrers()
    {
      $lists  =  Db::name('user')
              -> field('id,name')
              -> where('isReferrer=1')
              -> select(); 
      return $lists;
    }

    //获取平台统计数据 
    public function adminStatistics($arr)
    {
      if($arr['type'] == 1){//按天查询
          $dataType = "FROM_UNIXTIME(recordDate,'%Y-%m-%d')";
      }else{//按月查询
          $dataType = "FROM_UNIXTIME(recordDate,'%Y-%m')";
      }
      $all  =  db('shop_money_record')
              -> field($dataType.' as time,SUM(money) as totalMoney')
              ->where('recordDate','between',[$arr['start'],$arr['end']])
              -> group($dataType) 
              -> select();
      $yes  =  db('shop_money_record')
              -> field($dataType.' as time,SUM(money) as totalMoney')
              -> where('isOff=1')//支出
              ->where('recordDate','between',[$arr['start'],$arr['end']])
              -> group($dataType) 
              -> select();
      $no  =  db('shop_money_record')
              -> field($dataType.' as time,SUM(money) as totalMoney')
              -> where('isOff=0')//未出账
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

    //获取商户统计数据 
    public function shopStatistics($arr)
    {
      $where = $arr['shopId'] ? ['shopId' => $arr['shopId']] : '';
      if($arr['type'] == 1){//按天查询
          $dataType = "FROM_UNIXTIME(recordDate,'%Y-%m-%d')";
      }else{//按月查询
          $dataType = "FROM_UNIXTIME(recordDate,'%Y-%m')";
      }
      $all  =  db('shop_money_record')
              -> field($dataType.' as time,SUM(money) as totalMoney')
              -> where($where)
              ->where('recordDate','between',[$arr['start'],$arr['end']])
              -> group($dataType)
              -> select();
      $yes  =  db('shop_money_record')
              -> field($dataType.' as time,SUM(money) as totalMoney')
              -> where('isOff=1')//已出账
              -> where($where)
              ->where('recordDate','between',[$arr['start'],$arr['end']])
              -> group($dataType) 
              -> select();
      $no  =  db('shop_money_record')
              -> field($dataType.' as time,SUM(money) as totalMoney')
              -> where('isOff=0')//未出账
              -> where($where)
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

    //获取校区统计数据 
    public function schoolStatistics($arr)
    {
      $where = $arr['schoolId'] ? ['schoolId' => $arr['schoolId']] : '';
      if($arr['type'] == 1){//按天查询
          $dataType = "FROM_UNIXTIME(recordDate,'%Y-%m-%d')";
      }else{//按月查询
          $dataType = "FROM_UNIXTIME(recordDate,'%Y-%m')";
      }
      $all  =  db('shop_money_record')  //总收入
              -> field($dataType.' as time,SUM(money) as totalMoney')
              -> where($where)
              ->where('recordDate','between',[$arr['start'],$arr['end']])
              -> group($dataType)
              -> select();
      $yes  =  db('shop_money_record')  //已入账
              -> field($dataType.' as time,SUM(money) as totalMoney')
              -> where('isOff=1')
              -> where($where)
              ->where('recordDate','between',[$arr['start'],$arr['end']])
              -> group($dataType) 
              -> select();
      $no  =  db('shop_money_record') //未入账
              -> field($dataType.' as time,SUM(money) as totalMoney')
              -> where('isOff=0')
              -> where($where)
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

    //获取推荐人统计数据 
    public function referrerStatistics($arr)
    {
      if($arr['type'] == 1){//按天查询
          $dataType = "FROM_UNIXTIME(createTime,'%Y-%m-%d')";
      }else{//按月查询
          $dataType = "FROM_UNIXTIME(createTime,'%Y-%m')";
      }
      if($arr['referrerId']){
          $where['userId']=$arr['referrerId'];
      }
      $where['type']=8;
      $data =db('record_money')
              -> field($dataType.' as time,SUM(money) as totalMoney')
              -> where($where)
              ->where('createTime','between',[$arr['start'],$arr['end']])
              -> group($dataType)
              -> select(); 
      $res = $this->getDoDatas(['data'=>$data],$arr);
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
      
      $data =db('record_money')
              -> field($dataType.' as time,SUM(money) as totalMoney')
              -> where('type=9')
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
      if($arr['shopId']){
          $where1=$arr['schoolId']?'schoolId='.$arr['schoolId']:'';
          $userIds = db('user_benefit')
                  -> field('userId')
                  -> where($where1)
                  -> where(['shopId'=>$arr['shopId'],'isDelete'=>0])
                  -> select();
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