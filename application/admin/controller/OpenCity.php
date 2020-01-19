<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\Area as AreaModel;
use app\admin\model\ExistCity as ExistCityModel;
//use app\admin\model\AdminRole as AdminRoleModel;
//use think\Config;

/**
 *  已开通城市管理
 * Class OpenCity
 * @package app\admin\controller
 */
class OpenCity extends AdminBase
{
	  protected $open_city_model;

    protected function _initialize()
    {
        parent::_initialize();
        
        $this->open_city_model = new ExistCityModel(); 
    }
    
    /**
	 * 开通城市列表
	 * Class OpenCity
	 * @package app\admin\controller
	 */

    public function index()
    {
      $this->getAcessTypes();
      $info = db('exist_city')->select();    
      return $this->fetch('index', ['info' => $info]); 
    }


	/**
	 * 开通城市添加
	 * Class OpenCity
	 * @package app\admin\controller
	 */
	public function add()
    {
      $area_model   = new AreaModel();
		 if ($this->request->isPost()) {//数据提交并处理 
          $post['cityCode']=input('post.cityCode');
          $post['cityName']	= input('post.cityName');
          $res = $this->open_city_model->getSearchOpenCity($post);
          if($res){
             $this->error('该城市已存在','admin/OpenCity/index');
          }
          $post['createTime'] = time();       
		      if ($this->open_city_model->allowField(true)->save($post)) {
              $this->success('保存成功','admin/OpenCity/index');
          } else {
              $this->error('保存失败','admin/OpenCity/index');
          }		   
		  }else{			//添加页面展示    
            $provinces  = $area_model->province();
            return $this->fetch('add', ['provinces' => $provinces]); 
      }
         
    }

    /**
     * 开通城市
     * @param $id
     */
    public function edit($id)
    {     
        if ($this->open_city_model->update(['status'=>1,'id'=>$id])) {
            operateLog('编辑开通城市','open_city',$id,$this->admin_id);
            $this->success('开通成功','admin/OpenCity/index');
        } else {
            $this->error('开通失败','admin/OpenCity/index');
        }
    }

    /**
     * 停止开通城市
     * @param $id
     */
    public function del($id)
    {    	
        $res = $this->open_city_model->downOpenCity($id);
        if ($res) {
            $this->error('操作失败，该开通城市下有商户在使用','admin/OpenCity/index');
        } else {
            $s = $this->open_city_model->update(['status'=>2,'id'=>$id]);
            if($s){
                operateLog('停止开通城市','open_city',$id,$this->admin_id);
                $this->success('已停止开通该城市','admin/OpenCity/index');
            }
        }
    }


    /**
     * 获取对应省份下城市数据
     * 
     */

    public function getCitys($province_id)
    {
      $area_model   = new AreaModel();
      $citys  = $area_model->city($province_id);             
      $str = "<option value=''>--请选择城市--</option>";
      foreach ($citys as $val) {
          $str .= "<option value='".$val['district_code']."'>".$val['district_name']."</option>";
      }
      echo $str;
    }
	
}
