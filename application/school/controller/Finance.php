<?php
namespace app\school\controller;

use app\common\controller\AdminBase;
use app\common\model\SchoolCommon;
use app\school\model\Finance as FinanceModel;

/**
 *  财务统计管理
 * Class Adviser
 * @package app\school\controller
 */
class Finance extends AdminBase
{

    //校区收入统计
    public function school()
    {
      $data = $this->getdateDatas();    
      return $this->fetch('school', ['data' => $data]);
    }

    //顾问收入统计
    public function adviser()
    {
      $schoolId = $this->getAdminschoolId();
      $financeModel = new FinanceModel();
      $data = $this->getdateDatas();
      $advisers=$financeModel->getAdvisers($schoolId);     
      return $this->fetch('adviser', ['data' => $data,'advisers' => $advisers]);
    }

    //受益人收入统计
    public function benefit()
    {
      $schoolId = $this->getAdminschoolId();
      $financeModel = new FinanceModel();
      $data = $this->getdateDatas();
      $benefits=$financeModel->getBenefits($schoolId);     
      return $this->fetch('benefit', ['data' => $data,'benefits' => $benefits]);
    }

    // 获取校区Id     
    public function getAdminschoolId()
    {
        $SchoolCommonModel = new SchoolCommon();
        $schoolId = $SchoolCommonModel->getSchoolIdFromAdminId($this->admin_id);
        if (!$schoolId) {
            $this->error('该管理员没有指定校区!');
        }
        return $schoolId;
    }

    //顾问销售额统计图
    public function adviserMoney()
    {
      $schoolId = $this->getAdminschoolId();
      $financeModel = new FinanceModel();
      $data = $this->getdateDatas();
      $advisers=$financeModel->getAdvisers($schoolId);     
      return $this->fetch('adviserMoney', ['data' => $data,'advisers' => $advisers]);
    }

    //日期数据
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

    //判断顾问是否被修改
    public function getAdviserUpdateTime(){
      $adviserId=input('get.adviserId');
      $financeModel = new FinanceModel();
      $updateTime = $financeModel->adviserIsUpdate($adviserId);
      if($updateTime){
          $time=date('Y-m-d',$updateTime);
          return ['status'=>'ok','data'=>$time];
      }else{
          return ['status'=>'err','data'=>[]];
      }
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
            // $arr['end'] = strtotime($arr['bTime']);
        }
        $arr['schoolId'] = $this->getAdminschoolId();

        $model = new FinanceModel();
        switch($arr['action']){
            case 'school':
                $res = $model->schoolStatistics($arr);
            break;
            case 'benefit':
                $res = $model->benefitStatistics($arr);
            break;
            case 'adviser':
                $res = $model->adviserStatistics($arr);
            break;
            case 'adviserMoney':
                $res = $model->adviserMoneyStatistics($arr);
            break;
            default:
            $res=[];
        }
        return $res;
    }

}
