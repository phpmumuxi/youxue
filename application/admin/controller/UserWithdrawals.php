<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\UserWithdrawals as UserWithdrawalsModel;
use PHPExcel_IOFactory;
use PHPExcel;
/**
 *  用户提现管理
 * @package app\admin\controller
 */
class UserWithdrawals extends AdminBase
{
    protected $user_withdrawals_model;

    protected function _initialize()
    {
        parent::_initialize();        
        $this->user_withdrawals_model = new UserWithdrawalsModel();
    }

    //用户提现列表
    public function index()
    { 
      $ordertId = input('get.ordertId','-1');
      $exportId = input('get.exportId','-1');
      $phone = input('get.phone','');
      $orderStatus = [0 => '正在提现',1 => '提现成功',2 => '提现失败'];
      $exportStatus = [0 => '未处理',1 => '待处理',2 => '已处理',3 => '付款中'];
      $datas = $this->user_withdrawals_model-> getUserWithdrawalsOrders($ordertId,$phone,$exportId);
      $page = $datas->render();
      $lists=$datas->toArray();
      
      return $this->fetch('index', ['orderStatus' => $orderStatus,'exportStatus' => $exportStatus,'ordertId' => $ordertId,'exportId' => $exportId,'phone' => $phone,'lists' => $lists,'page' => $page]);           
    }

    //导出(信息列表)
    public function export()
    {
      $datas = $this->user_withdrawals_model->exportMoneys();
      if(!$datas){
        $this->error('没有数据，不能导出');
      }
      $this->exportExcel($datas);
    }

    //导出Excel，浏览器本地保存
    public function exportExcel($datas)
    {
      $objPHPExcel = new PHPExcel();
      $sheet = $objPHPExcel ->getActiveSheet(0);
      $sheet->setTitle('清单');
      $header = ['付方账号','金额上限','生效日期','失效日期','支票权限','授权使用人','收方信息填写类型','收方账号','收方户名','汇路类型','收方行名称','收方行行号','收方行地址','附言','收款人手机号码'];
      $headerSize = [19,12,12,14,23,19,27,21,32,11,14,24,12,7,19];
      
      $s = ord('A');
      foreach($header as $k=>$val){
        $sheet->setCellValue(chr($s+$k).'1',$val);
        if(isset($headerSize[$k]))
          $sheet->getColumnDimension(chr($s+$k))->setWidth($headerSize[$k]);
        else
          $sheet->getColumnDimension(chr($s+$k))->setAutoSize(true);
      }

      $now = date('Ymd');
      $i = 2;
      $ids='';
      foreach($datas as $k=>$v){
        $sheet->setCellValueExplicit('A'.$i,'125907775810801',\PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValue('B'.$i,$v['money']);
        $sheet->setCellValue('C'.$i,$now);
        $sheet->setCellValue('D'.$i,$now);
        $sheet->setCellValue('E'.$i,'可支付、不可转让');
        $sheet->setCellValue('F'.$i,'江苏人之初1');
        $sheet->setCellValue('G'.$i,'预先录入(支付时不可修改)');

        $sheet->setCellValueExplicit('H'.$i,$v['bankCard'],\PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValue('I'.$i,$v['bankUserName']);

        $bn = $v['bankName'];
        $s = mb_strpos($bn,'银行',0,'utf-8');
        if($s>0){
          $str = mb_substr($bn,0, $s + 2 ,'utf-8');
          $v['bankName'] = $str;
        }
        if($v['bankName'] == '招商银行'){
            $sheet->setCellValue('J'.$i,'招商银行');
        }else{
            if($v['money'] > 50000)
              $sheet->setCellValue('J'.$i,'他行普通');
            else
              $sheet->setCellValue('J'.$i,'他行实时');
          }
        $sheet->setCellValue('K'.$i,$v['bankName']);
        $sheet->setCellValue('O'.$i,$v['bankPhone']);
        $ids .= $v['id'].',';
        $i++;
      }
      $ids .='0';
      db('money_bank')->where('id in ('.$ids.')')->update(['exportStatus'=>1]);
      operateLog('用户提现(导出)','money_bank',0,$this->admin_id);
      //直接输出到浏览器
      $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
      header("Pragma: public");
      header("Expires: 0");
      header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
      header("Content-Type:application/force-download");
      header("Content-Type:application/vnd.ms-execl");
      header("Content-Type:application/octet-stream");
      header("Content-Type:application/download");
      $outfilename = "用户-".date('Ymd-H.i',time());
      header('Content-Disposition:attachment;filename="'.$outfilename.'.xls"');
      header("Content-Transfer-Encoding:binary");
      $objWriter->save('php://output');
    }

    //付款中
    public function inPay()
    {
      $re = $this->user_withdrawals_model->getExportDatas(1);
      
      if($re=='no'){          
          return ['status'=>'no','msg'=>'没有数据!'];
      }elseif($re=='ok'){
          operateLog('用户提现(付款中)','money_bank',0,$this->admin_id);
          return ['status'=>'ok','msg'=>'成功!'];
      }else{
          return ['status'=>'err','msg'=>'失败!'];
      }
    }

    //确认导出信息成功
    public function confirm()
    {        
      $re = $this->user_withdrawals_model->getExportDatas(3,$this->admin_id);
      
      if($re=='no'){          
          return ['status'=>'no','msg'=>'没有需要确认的数据!'];
      }elseif($re=='ok'){
          operateLog('用户提现(确认)','money_bank',0,$this->admin_id);
          return ['status'=>'ok','msg'=>'成功!'];
      }else{
          return ['status'=>'err','msg'=>'失败!'];
      }
    }

    //提现失败
    public function lose()
    {
        $id = input('post.id');
        $remarks = input('post.remarks');
        $re = $this->user_withdrawals_model->LoseMoneyDatas($id,$this->admin_id,$remarks);
        if($re=='no'){          
            return ['status'=>'no','msg'=>'没有数据!'];
        }elseif($re=='ok'){
            operateLog('提现失败','money_bank',$id,$this->admin_id);
            return ['status'=>'ok','msg'=>'成功!'];
        }else{
            return ['status'=>'err','msg'=>'失败!'];
        }
    }

}
