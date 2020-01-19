<?php
namespace app\shop\controller;

use app\common\controller\AdminBase;
use app\shop\model\ShopMoneyRecord as ShopMoneyRecordModel;
use app\common\model\ShopCommon as ShopCommonModel;

/**
 *  商户后台管理
 * Class Admin
 * @package app\admin\controller
 */
class Finance extends AdminBase
{
	  //商户账单列表   
    public function bill()
    {
       $data=[];
       $data['startTime']=input('get.startTime');
       $data['endTime']=input('get.endTime');
    
       if(!$data['startTime'] && !$data['endTime']){
          $data = [            
                    'startTime'=>date('Y-m-d', strtotime('-7 days')),
                    'endTime'=>date('Y-m-d',strtotime("-1 day")),         
                ];
       }
       
       // 获取商户总收入
       $shopId = $this->getAdminShopId();
       $shop_money_record_model=new ShopMoneyRecordModel();
       $totalMoney = $shop_money_record_model->getShopTotalMoney($shopId,$data);
       $this->assign('totalMoney', $totalMoney);

       $datas = $shop_money_record_model-> getShopBills($shopId,$data); 
       $page = $datas->render();  
       $lists = $datas->toArray();
       return $this->fetch('bill', ['lists' => $lists,'data' => $data,'page' => $page]);
    }

    //商户账单详情  
    public function billDetail($id)
    {
       $shop_money_record_model=new ShopMoneyRecordModel();
       //优选课程 
       $datas = $shop_money_record_model->getBillClassDetail($id,1);
       $page = $datas->render();  
       $lists = $datas->toArray();
       //万人砍课程 
       $infos = $shop_money_record_model->getBillClassDetail($id,2);
       $page1 = $infos->render();  
       $lists1 = $infos->toArray();
       return $this->fetch('bill_detail', ['lists' => $lists,'page' => $page,'lists1' => $lists1,'page1' => $page1]);
    }
	   
     //商户财务统计    
    public function shopStatistics()
    {  
      $data = $this->getdateDatas();
      return $this->fetch('shopStatistics', ['data' => $data['data'],'schools' => $data['schools']]);
    }

    //顾问收入统计图
    public function adviserStatistics()
    {       
      $data = $this->getdateDatas();
      return $this->fetch('adviserStatistics', ['data' => $data['data'],'schools' => $data['schools']]);
    }

    //受益人收入统计图
    public function benefitStatistics()
    {       
      $data = $this->getdateDatas();
      return $this->fetch('benefitStatistics', ['data' => $data['data'],'schools' => $data['schools']]);
    }
    
    //顾问销售额统计图
    public function adviserMoneyStatistics()
    {       
      $data = $this->getdateDatas();
      return $this->fetch('adviserMoneyStatistics', ['data' => $data['data'],'schools' => $data['schools']]);
    }

    /*请求数据*/
    public function dataAjax(){        
        $arr=input('get.');

        $arr['shopId'] = $this->getAdminShopId();
        $arr['start'] = strtotime($arr['aTime']);
        
        if($arr['type'] == 1){//按天查询
            $arr['end'] = strtotime($arr['bTime'])+86399;
        }else{//按月查询
            $t=$arr['bTime'].'-01';            
            $arr['end'] = strtotime("$t +1 month")-1;
            // $arr['end'] = strtotime($arr['bTime']);
        }
        $model = new ShopMoneyRecordModel();
        switch($arr['action']){
            case 'shop':
                $res = $model->shopStatistics($arr);
            break;
            case 'adviser':
                $res = $model->adviserStatistics($arr);
            break;
            case 'benefit':
                $res = $model->benefitStatistics($arr);
            break;
            case 'adviserMoney':
                $res = $model->adviserMoneyStatistics($arr);
            break;
            default:
            $res=[];
        }
        return ['data'=>$res];
    }

    public function getdateDatas(){
      $year=date('Y',time());
      $month=date('m',time());
      $data=[];      
      $data['data'] = [            
            'sTime'=>date('Y-m-d', strtotime('-7 days')),
            'eTime'=>date('Y-m-d',strtotime("-1 day")),
            'ssTime'=>$year.'-'.($month>3?'0'.($month-3):'01'),
            // 'eeTime'=>$year.'-'.$month
            'eeTime'=>date('Y-m',strtotime('-1 month'))        
      ];
      $shopId = $this->getAdminShopId();
      $shop_money_record_model = new ShopMoneyRecordModel();
      $data['schools'] = $shop_money_record_model->getShopHasSchools($shopId);
      return $data;
    }

     // 获取商户Id     
    public function getAdminShopId()
    {
        $shop_common_model=new ShopCommonModel();
        $shopId=$shop_common_model->getShopIdFromAdminId($this->admin_id);
        if (!$shopId) {
            $this->error('该管理员没有指定商户!');
        }
        return $shopId;
    }

    //顾问销售额管理
    public function adviserMoney()
    { 
      $date = [            
        'sTime'=>input('get.sTime',date('Y-m-d', strtotime('-7 days'))),
        'eTime'=>input('get.eTime',date('Y-m-d', strtotime('-1 days')))        
        ];
      $schoolId = input('get.schoolId',0);
      $shopId = $this->getAdminShopId();
      $adviser_model = new ShopMoneyRecordModel();
      $schools = $adviser_model->getShopHasSchools($shopId); 
      $datas = $adviser_model->getAdviserMoneyLists($shopId,$schoolId,$date);
      $page = $datas->render();
      $lists = $adviser_model->getAdviserMoneyDatas($datas->toArray(),$date,$shopId);
      return $this->fetch('adviserMoney', ['date' => $date,'schools' => $schools,'schoolId' => $schoolId,'lists' => $lists,'page' => $page]);
    }

    //顾问销售额账单详情  
    public function adviserMoneyBill()
    {
       $arr=input('param.');
       $adviser_model = new ShopMoneyRecordModel();

       //优选课程
       $datas = $adviser_model->getBillMoneyDetail($arr,1);
       $page = $datas->render();  
       $lists = $datas->toArray();
       //万人砍课程 
       $infos = $adviser_model->getBillMoneyDetail($arr,2);
       $page1 = $infos->render();  
       $lists1 = $infos->toArray();           
       return $this->fetch('adviserMoneyBill', ['lists'=>$lists,'page'=>$page,'lists1'=>$lists1,'page1'=>$page1]);
    }

}
