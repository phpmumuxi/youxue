<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\FeedBack as FeedBackModel;
/**
 *  意见反馈管理
 * @package app\admin\controller
 */
class FeedBack extends AdminBase
{
    //意见反馈列表
    public function index()
    { 
      
      $phone = input('get.phone','');
      $model= new FeedBackModel();
      $datas = $model-> getUserFeedBack($phone);
      $page = $datas->render();
      $lists=$datas->toArray();
      
      return $this->fetch('index', ['phone' => $phone,'lists' => $lists,'page' => $page]);           
    }

}
