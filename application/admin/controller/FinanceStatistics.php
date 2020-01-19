<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\ShopMoneyRecord as ShopMoneyRecordModel;

//财务统计
class FinanceStatistics extends AdminBase
{

    //平台总收入统计图
    public function adminStatistics()
    { 
      $data = $this->getdateDatas();
      return $this->fetch('adminStatistics', ['data' => $data]);
    }

    //商户收入统计图
    public function shopStatistics()
    { 
      $data = $this->getdateDatas();      
      $model = new ShopMoneyRecordModel();      
      $lists = $model->getShops();
      return $this->fetch('shopStatistics', ['data' => $data,'lists' => $lists]);
    }

    //校区收入统计图
    public function schoolStatistics()
    { 
      $data = $this->getdateDatas();      
      $model = new ShopMoneyRecordModel();      
      $lists = $model->getSchools();
      return $this->fetch('schoolStatistics', ['data' => $data,'lists' => $lists]);
    }

    //推荐人收入统计图
    public function referrerStatistics()
    { 
      $data = $this->getdateDatas();
      $model = new ShopMoneyRecordModel();      
      $lists = $model->getReferrers();
      return $this->fetch('referrerStatistics', ['data' => $data,'lists' => $lists]);
    }

    //顾问收入统计图
    public function adviserStatistics()
    {       
      $data = $this->getdateDatas();
      return $this->fetch('adviserStatistics', ['data' => $data]);
    }

    //受益人收入统计图
    public function benefitStatistics()
    {       
      $data = $this->getdateDatas();
      $model = new ShopMoneyRecordModel();      
      $lists = $model->getShops();
      return $this->fetch('benefitStatistics', ['data' => $data,'lists' => $lists]);
    }

    public function getdateDatas(){
      $year=date('Y',time());
      $month=date('m',time());      
      $data = [            
            'sTime'=>date('Y-m-d', strtotime('-7 days')),
            'eTime'=>date('Y-m-d',strtotime("-1 day")),
            'ssTime'=>$year.'-'.($month>3?'0'.($month-3):'01'),
            // 'eeTime'=>$year.'-'.$month
            'eeTime'=>date('Y-m',strtotime('-1 month'))        
      ];
      return $data;
    }

    // 获取校区数据
    public function getSchoolAjax()
    {
      $id=input('get.id/d');
      $lists  =  db('school')
              -> field('id,name')
              -> where(['status'=>1,'shopId'=>$id])
              -> select();
      $str ="<select id='schoolId' style='border:0;background:white none repeat scroll 0 0;'>";              
      $str .= "<option value='0'>选择校区</option>";
      foreach ($lists as $val) {
          $str .= "<option value='".$val['id']."'>".$val['name']."</option>";        
      }
      $str .= "</select>";
      echo $str;
    }

    /*请求数据*/
    public function dataAjax(){        
        $arr=input('get.');  

        $arr['start'] = strtotime($arr['aTime']);
        
        if($arr['type'] == 1){//按天查询
            $arr['end'] = strtotime($arr['bTime'])+86399;
        }else{//按月查询
            $t=$arr['bTime'].'-01';            
            $arr['end'] = strtotime("$t +1 month")-1;
            //$arr['end'] = strtotime($arr['bTime']);
        }
        $model = new ShopMoneyRecordModel();
        switch($arr['action']){
            case 'admin':
                $res = $model->adminStatistics($arr);
            break;
            case 'shop':
                $res = $model->shopStatistics($arr);
            break;
            case 'referrer':
                $res = $model->referrerStatistics($arr);
            break;
            case 'school':
                $res = $model->schoolStatistics($arr);
            break;
            case 'adviser':
                $res = $model->adviserStatistics($arr);
            break;
            case 'benefit':
                $res = $model->benefitStatistics($arr);
            break;
            default:
            $res=[];
        }
        return ['data'=>$res];
    }

}
