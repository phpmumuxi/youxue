<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\Cmbc as CmbcModel;

/**
 *  招商码管理
 * @package app\Cmbc\controller
 */
class Cmbc extends AdminBase
{
    // 招商码列表
    public function index()
    {
        $phone = input('get.phone','');
        $status = input('get.status','-1');
        $useStatus = [0 => '未使用',1 => '已使用'];
        $cmbcModel = new CmbcModel();
        $data = $cmbcModel->getUserCmbcs($phone,$status);
        $page = $data->render();
        $lists = $data->toArray();
        return $this->fetch('index', ['phone' => $phone,'useStatus' => $useStatus,'status' => $status,'lists' => $lists,'page' => $page]);
    }


  	// 招商码添加
  	public function add()
    {
        $num=input('post.cmbcNum');
        $term=input('post.cmbcTerm');
        $data=[];
        for ($i=0; $i <$num ; $i++) { 
          $data[]=$this->makeCmbcCode();
        }
        $cmbcModel = new CmbcModel();
        $re = $cmbcModel->addUserCmbcCode($term,$data);
        if ($re) {
            return  ['status'=>'ok','msg'=>'生成成功！'];
        } else {
            return  ['status'=>'err','msg'=>'生成失败！'];
        }    
		 
    }

    // 删除招商码
    public function del()
    {   	
        $id=input('post.id');
        if (db('cmbc_code')->where('id',$id)->update(['isDelete'=>1])) {
            operateLog('删除招商码','cmbc_code',$id,$this->admin_id);
            return  ['status'=>'ok','msg'=>'删除成功！'];
        } else {
            return  ['status'=>'err','msg'=>'删除失败！'];
        }
    }

    // 生成招商码（6位）
    public function makeCmbcCode()
    {
        // $str='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        $str='abcdefghijklmnopqrstuvwxyz1234567890';
        $checkstr='';
        $len=strlen($str)-1;
        for ($i=0;$i<6;$i++)
        {
          $num=mt_rand(0,$len);
          $checkstr.=$str[$num];
        }
        return $checkstr;
    }

}
