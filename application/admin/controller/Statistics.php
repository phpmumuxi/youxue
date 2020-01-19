<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\Statistics as StatisticsModel;

//统计概况
class Statistics extends AdminBase
{ 
    //销售额统计
    public function money()
    { 
      $data = $this->getdateDatas();      
      $model = new StatisticsModel();      
      $shops = $model->getShops();
      return $this->fetch('money', ['data' => $data,'shops' => $shops]);
    }

    //顾问销售额统计
    public function adviser()
    { 
      $data = $this->getdateDatas();      
      $model = new StatisticsModel();      
      $shops = $model->getShops();
      return $this->fetch('adviser', ['data' => $data,'shops' => $shops]);
    }

    public function getdateDatas(){
      $year=date('Y',time());
      $month=date('m',time());
      $data = [            
            'sTime'=>date('Y-m-d', strtotime('-7 days')),
            'eTime'=>date('Y-m-d',strtotime("-1 day")),
            'ssTime'=>$year.'-'.($month>3?'0'.($month-3):'01'),
            'eeTime'=>date('Y-m',strtotime('-1 month'))
      ];
      return $data;
    }

    //品牌销售额柱状图
    public function brand()
    { 
      $data = $this->getdateDatas();
      return $this->fetch('brand', ['data' => $data]);
    }

    //课程销售情况
    public function course()
    {    
       $data=[];
       $data['startTime']=date('Y-m-d', strtotime('-7 days'));
       $data['endTime']=date('Y-m-d',strtotime("-1 day"));       
       $data['maxTime']=$data['endTime'];
       $data['shopId']=0;
       if(input('?get.shopId')){
          $data['shopId']=input('get.shopId');
       }   
       if(input('?get.startTime') && input('?get.endTime')){
          $data['startTime']=input('get.startTime');
          $data['endTime']=input('get.endTime');
       }
      $model = new StatisticsModel();
      $shops = $model->getShops();
      $res = $model->getCourseSales($data);
      $page = $res->render();
      $lists = $res->toArray();
      return $this->fetch('course', ['shops' => $shops,'lists' => $lists,'page' => $page,'data' => $data]);
    }

    //地图
    public function map()
    {      
      return $this->fetch();
    }

    public function dataAjax(){
      $arr=input('get.');  
      $model = new StatisticsModel();
      
      switch($arr['action']){
            case 'map':
                $res = $model->getSchoolMap();
            break;
            case 'brand':
                $res = $model->brandStatistics($arr);
            break;
            case 'money':
                $res = $model->moneyStatistics($arr);
            break;
            case 'adviser':
                $res = $model->adviserStatistics($arr);
            break;
            default:
            $res=[];
        }
        return $res;
    }

    //获取校区数据
    public function getSchoolAjaxDatas(){
        $id=input('post.id/d');        
        $model = new StatisticsModel();
        $lists = $model->getSchoolMap($id);
        $str = "<option value='0'>选择校区</option>";
        foreach ($lists as $val) {
            $str .= "<option value='".$val['id']."'>".$val['name']."</option>";        
        }
        echo $str;
    }

    //获取顾问数据
    public function getAdviserAjaxDatas(){
        $id=input('post.id/d');
        $model = new StatisticsModel();
        $lists = $model->getSchoolAdviser($id);
        $str = "<option value='0'>选择顾问</option>";
        foreach ($lists as $val) {
            $str .= "<option value='".$val['id']."'>".$val['name']."</option>";        
        }
        echo $str;
    }

}
