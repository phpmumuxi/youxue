<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\ShopMoneyRecord as ShopMoneyRecordModel;
use PHPExcel_IOFactory;
use PHPExcel;
/**
 *  到账管理
 * @package app\admin\controller
 */
class ArrivalManagement extends AdminBase
{
    protected $shop_money_record_model;

    protected function _initialize()
    {
        parent::_initialize();        
        $this->shop_money_record_model = new ShopMoneyRecordModel();
    }

  /**
	 * 到账管理(信息列表)
	 * @package app\admin\controller
	 */

    public function index()
    { 
      $start_time = input('get.start_time');
      if($start_time){
        $arr['start_time'] = strtotime($start_time);
      }else{
        $arr['start_time'] = time();
      }
      $type = input('get.type',1);
      
      $arr['type'] = $type;
      $arr['bankType'] = 1;//对公
      $publicData = $this->shop_money_record_model->ArrivalOutMoney($arr,false);

      $arr['bankType'] = 2;//私人
      $privateData = $this->shop_money_record_model->ArrivalOutMoney($arr,true);

      $publicPage = $publicData->render();
      $publicData=$publicData->toArray();

      $privatePage = $privateData->render();
      $privateData=$privateData->toArray();
      
      return $this->fetch('index', ['data' => $arr,'publicData' => $publicData,'privateData' => $privateData,'publicPage' => $publicPage,'privatePage' => $privatePage]);           
    }

    /**
   * 导出管理(信息列表)
   * @package app\admin\controller
   */
    public function export()
    {
      $type=input("get.type");
      $datas = $this->shop_money_record_model->ArrivalexportMoneys($type);
      if(!$datas){
        $this->error('没有数据，不能导出','/admin/ArrivalManagement/index');
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
        $sheet->setCellValue('B'.$i,($v['money']*0.994));
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
        $ids .= $v['rid'].',';
        $i++;
      }
      $ids .='0';
      $this->shop_money_record_model->where('id in ('.$ids.')')->update(['status'=>1]);
      operateLog('导出管理(账单)','shop_money_record',0,$this->admin_id);
      //直接输出到浏览器
      $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
      header("Pragma: public");
      header("Expires: 0");
      header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
      header("Content-Type:application/force-download");
      header("Content-Type:application/vnd.ms-execl");
      header("Content-Type:application/octet-stream");
      header("Content-Type:application/download");
      $outfilename = "商户-".date('Ymd-H.i',time());
      header('Content-Disposition:attachment;filename="'.$outfilename.'.xls"');
      header("Content-Transfer-Encoding:binary");
      $objWriter->save('php://output');
    }

    /**
   * 确认导出信息成功
   * @package app\admin\controller
   */
    public function confirm($type)
    {
      set_time_limit(180);
      $datas = $this->shop_money_record_model->ArrivalexportMoneys($type);
      
      $id= implode(',',array_column($datas, 'id'));
      
      if(!$id){
        $this->error('没有需要确认的数据','/admin/ArrivalManagement/index');
      }

      //获取各个商户的余额
      $oldmoney = $this->shop_money_record_model->getShopMoney($id);
      
      $money = [];//商户余额
      foreach($oldmoney as $key=>$v){
        $money[$v['id']] = $v['money'];//每个商户的余额
      }
      
      $arr=[];
      $ids = '';//导出的商户收入记录id
      foreach($datas as $key=>$vo){
        $shopid = $vo['id'];
        if($money[$shopid]-$vo['money'] >= 0){
            $arr[$key]['shopId'] = $shopid;
            $arr[$key]['nowMoney'] = $money[$shopid];
            $arr[$key]['changeMoney'] = $money[$shopid]-$vo['money'];
            $arr[$key]['money'] = $vo['money'];
            $arr[$key]['createTime'] = time();
            $arr[$key]['typeId'] = $vo['rid'];
            $arr[$key]['type'] = 2;
            $money[$shopid] -= $vo['money'];//每个商户的余额递减
            $ids .= $vo['rid'].',';
        }else{
            $this->error('金额错误，余额小于导出金额！','/admin/ArrivalManagement/index');
        }
      }
      $ids .= '0';

      //更新商户余额
      $moneyArr=[];
      $i=0;
      foreach($money as $k=>$vv){
        $moneyArr[$i]['id'] = $k;
        $moneyArr[$i]['money'] = $vv;
        $i++;
      }

      //数据确认成功  各个表状态处理
      $res = $this->shop_money_record_model->doDatasRecord($arr,$moneyArr,$ids);
      if($res){
        operateLog('数据确认(账单)','shop_money_record',0,$this->admin_id);
        $this->success('数据确认成功','/admin/ArrivalManagement/index');
      }else{
        $this->error('数据确认失败','/admin/ArrivalManagement/index');
      }

    }

}
