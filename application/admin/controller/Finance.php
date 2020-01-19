<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\RecordMoney as RecordMoneyModel;
use PHPExcel_IOFactory;
use PHPExcel;
//财务管理
class Finance extends AdminBase
{
    //商户财务收入列表
    public function index()
    { 
       $shopName = input('get.shopName');
       $shopName ? $this->assign('shopName', $shopName) : '';
       $record_money_model = new RecordMoneyModel();
       //商户财务收入情况
       $datas = $record_money_model->getShopsMoneysFinance($shopName);
       $page = $datas->render();
       $lists = $record_money_model->doShopMoneyDatas($datas->toArray());
       return $this->fetch('index', ['lists' => $lists,'page' => $page]);  
    }

    //顾问个人财务管理
    public function adviser()
    {
      $data=[];
      $data['phone'] = '';
      $data['shopId'] = 0;
      $data['schoolId'] = 0;
      if(input('?get.phone')){
        $data['phone']=input('get.phone');
      }
      if(input('?get.shopId')){
        $data['shopId']=input('get.shopId');
      }
      if(input('?get.schoolId')){
        $data['schoolId']=input('get.schoolId');
      }
      $record_money_model = new RecordMoneyModel();
      $shops = $record_money_model->getShopLists();
      $schools = $record_money_model->getSchoolLists();
      //顾问列表
      $datas = $record_money_model->getAdviserLists($data);
      $page = $datas->render();
      $lists = $record_money_model->doDataMoney($datas->toArray());
      
      return $this->fetch('adviser', ['data' => $data,'shops' => $shops,'schools' => $schools,'lists' => $lists,'page' => $page]);
    }

    //某商户所有校区财务详情
    public function shopBill($id)
    {
      $record_money_model = new RecordMoneyModel();      
      $datas = $record_money_model->getShopBills($id);
      $page = $datas->render();
      $lists = $record_money_model->doShopBillDatas($datas->toArray(),$id);
      return $this->fetch('shopBill', ['lists' => $lists,'page' => $page]);
    }
    
    //某顾问实际收入（被替换）
    public function adviserNewMoney()
    {
      $post=input('post.');
      $time=date('Y-m-d H:i:s',$post['updateTime']);
      $record_money_model = new RecordMoneyModel();      
      $datas = $record_money_model->getAdviserNewMoney($post);
      return ['money'=>$datas,'time'=>$time];
    }

    //顾问销售额管理
    public function adviserMoney()
    { 
      $date = [            
        'sTime'=>input('get.sTime',date('Y-m-d', strtotime('-7 days'))),
        'eTime'=>input('get.eTime',date('Y-m-d', strtotime('-1 days'))),
        'shopId'=>input('get.shopId',0),        
        'schoolId'=>input('get.schoolId',0),        
        ];

      $record_money_model = new RecordMoneyModel();      
      $shops = $record_money_model->getShopLists();
      $schools = $record_money_model->getSchoolLists();
      $datas = $record_money_model->getAdviserMoneyLists($date);
      $page = $datas->render();
      $lists = $record_money_model->getAdviserMoneyDatas($datas->toArray(),$date);
      return $this->fetch('adviserMoney', ['shops' => $shops,'schools' => $schools,'date' => $date,'lists' => $lists,'page' => $page]);
    }

    //顾问销售额账单详情  
    public function adviserMoneyBill()
    {
       $arr=input('param.');
       $record_money_model = new RecordMoneyModel();
       $arr['status']=input('get.status',0);

       $userStatus=['0'=>'用户状态','1'=>'老用户','2'=>'新用户'];
       //优选课程
       $datas = $record_money_model->getBillMoneyDetail($arr,1);
       $page = $datas->render();  
       $lists = $datas->toArray();
       //万人砍课程 
       $infos = $record_money_model->getBillMoneyDetail($arr,2);
       $page1 = $infos->render();  
       $lists1 = $infos->toArray();           
       return $this->fetch('adviserMoneyBill', ['userStatus'=>$userStatus,'status'=>$arr['status'],'lists'=>$lists,'page'=>$page,'lists1'=>$lists1,'page1'=>$page1]);
    }

    //顾问销售额 导出
    public function adviserMoneyExport()
    {
        $arr=input('get.');
        $record_money_model = new RecordMoneyModel();
        $datas = $record_money_model->adviserMoneyDatas($arr);
        if(!$datas){
          $this->error('没有数据，不能导出','/admin/Finance/adviserMoney');
        }
          $this->exportExcel($datas);
    }

    //导出Excel，浏览器本地保存
    public function exportExcel($datas)
    {
      $objPHPExcel = new PHPExcel();
      $sheet = $objPHPExcel ->getActiveSheet(0);
      $sheet->setTitle('顾问销售额');
      $header = ['顾问姓名','手机号','所属商户','所属校区','总额(元)','新用户(元)','老用户(元)'];
      $headerSize = [16,18,28,28,20,20,20];
      
      $s = ord('A');
      foreach($header as $k=>$val){
        $sheet->setCellValue(chr($s+$k).'1',$val);
        if(isset($headerSize[$k]))
          $sheet->getColumnDimension(chr($s+$k))->setWidth($headerSize[$k]);
        else
          $sheet->getColumnDimension(chr($s+$k))->setAutoSize(true);
      }
 
      $i = 2;
      foreach($datas as $k=>$v){
        $sheet->setCellValueExplicit('A'.$i,$v['name']);
        $sheet->setCellValue('B'.$i,$v['phone']);
        $sheet->setCellValue('C'.$i,$v['shopName']);
        $sheet->setCellValue('D'.$i,$v['schoolName']);
        $sheet->setCellValue('E'.$i,$v['allMoney']);
        $sheet->setCellValue('F'.$i,$v['newMoney']);
        $sheet->setCellValue('G'.$i,$v['oldMoney']);
        $i++;  
      }
     
      operateLog('导出顾问销售额','adviser_name',0,$this->admin_id);
      //直接输出到浏览器
      $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
      header("Pragma: public");
      header("Expires: 0");
      header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
      header("Content-Type:application/force-download");
      header("Content-Type:application/vnd.ms-execl");
      header("Content-Type:application/octet-stream");
      header("Content-Type:application/download");
      $outfilename = "顾问销售额-".date('Ymd-H.i',time());
      header('Content-Disposition:attachment;filename="'.$outfilename.'.xls"');
      header("Content-Transfer-Encoding:binary");
      $objWriter->save('php://output');
    }

    //平台收入列表
    public function adminMoney()
    { 
       $date = [            
        'sTime'=>input('get.sTime',date('Y-m-d', strtotime('-7 days'))),
        'eTime'=>input('get.eTime',date('Y-m-d', strtotime('-1 days'))),       
        'shopId'=>input('get.shopId',0)
        ];

       $record_money_model = new RecordMoneyModel();       
       $totalData = $record_money_model->getAdminTotalMoney($date);
       $shops = $record_money_model->getShopLists();

       $datas = $record_money_model->getShopsMoneysList($date);
       $page = $datas->render();
       $lists = $record_money_model->doShopMoneyList($datas->toArray(),$date);
       return $this->fetch('adminMoney', ['date' => $date,'totalData' => $totalData,'shops' => $shops,'lists' => $lists,'page' => $page]);  
    }

  //商户账单详情(所有校区财务详情)
  public function adminMoneyBill()
  {
      $arr=input('param.');
      $schoolId=input('get.schoolId',0);
      $record_money_model = new RecordMoneyModel();
      $schools = $record_money_model->getSchoolLists($arr['id']);
      $datas = $record_money_model->getShopSchoolList($arr['id'],$schoolId);
      $page = $datas->render();
      $lists = $record_money_model->doAdminMoneyShopBill($datas->toArray(),$arr);
      return $this->fetch('adminMoneyBill', ['datas' => $arr,'schoolId' => $schoolId,'schools' => $schools,'lists' => $lists,'page' => $page]);
  }

  //校区每笔账单详情
  public function adminMoneyDetailBill()
  {
     $arr=input('param.');
     $record_money_model = new RecordMoneyModel();
     $arr['status']=input('get.status',0);

     $userStatus=['0'=>'用户状态','1'=>'老用户','2'=>'新用户'];
     //优选课程
     $datas = $record_money_model->getAdminMoneyDetailBill($arr,1);
     $page = $datas->render();  
     $lists = $datas->toArray();
     //万人砍课程 
     $infos = $record_money_model->getAdminMoneyDetailBill($arr,2);
     $page1 = $infos->render();  
     $lists1 = $infos->toArray();           
     return $this->fetch('adminMoneyDetailBill', ['datas' => $arr,'userStatus'=>$userStatus,'status'=>$arr['status'],'lists'=>$lists,'page'=>$page,'lists1'=>$lists1,'page1'=>$page1]);
  }

}
