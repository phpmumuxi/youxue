<?php
namespace app\shop\controller;

use app\common\controller\AdminBase;
use app\shop\model\Admin as AdminModel;
use app\shop\model\AdminAccess as AdminAccessModel;
use think\Config;

/**
 *  商户后台管理员列表管理
 * Class Admin
 * @package app\admin\controller
 */
class Admin extends AdminBase
{
	protected $admin_model;

    protected function _initialize()
    {
        parent::_initialize();        
        
        $this->admin_model = new AdminModel(); 
    }
    
    /**
	 * 管理员列表
	 * Class Admin
	 * @package app\admin\controller
	 */

    public function index()
    {
      $schoolId=input('get.schoolId','0');
      $infos = db('admin') ->field('shopId')-> find($this->admin_id);
      $schools  =  $this->admin_model->getAdminSchools($infos['shopId']);
      $data=$this->admin_model->getShopsAdmin($this->admin_id,$schoolId); 
      $page = $data->render();
      $info = $data->toArray();        
      return $this->fetch('index', ['schoolId' => $schoolId,'schools' => $schools,'page' => $page,'info' => $info]);
    }


	/**
	 * 管理员添加
	 * Class Admin
	 * @package app\admin\controller
	 */
	public function add()
    {
      $infos = db('admin') ->field('shopId')-> find($this->admin_id);
     if ($this->request->isPost()) {           
           $post=input('post.');           
           $post['name']=input('post.name');
           $post['createTime']=time();
           $post['adminId']=$this->admin_id;
           $post['password'] = md5(input('post.password') . Config::get('ValidationKey'));
           $info = db('admin')
                ->where(['name'=>$post['name'],'password'=>$post['password'],'isDelete'=>1])
                ->find();
           if($info){
              $this->error('用户已存在','shop/Admin/index');
           }
           $post['schoolId'] = input('post.schoolId');
           if($post['schoolId']){
              $post['type'] = 3;
              $post['roleId'] = 3;
           }else{
              $post['type'] = 2;
              $post['roleId'] = 2;
           }
           $post['shopId'] = $infos['shopId'];
		      if ($this->admin_model->allowField(true)->save($post)) {
              $adminId=$this->admin_model->id;              
              $this->redirect('shop/Admin/auth',['id'=>$adminId]);              
          } else {
              $this->error('保存失败','shop/Admin/add');
          }		   
		  }else{
            $schools  =  $this->admin_model->getAdminSchools($infos['shopId']);
            return $this->fetch('add', ['schools' => $schools]); 
      }
         
    }

    /**
     * 删除管理员
     * @param $id
     */
    public function del($id)
    {    	
        if ($this->admin_model->update(['isDelete'=>0,'id'=>$id])) {
            operateLog('删除管理员','admin',$id,$this->admin_id);
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }


    /**
     * 获取权限数据
     * return []
     */
    public function auth($id)
    {   
        if(!$id){
          $this->error('参数错误！');
        }
        $accessModel = new AdminAccessModel();
        if ($this->request->isPost()) {//数据提交并处理 
           $post['uid']  = $id;          
           $post['ids'] =  input('post.ids/a');
           if($post['ids']){
             $post['ids']=implode($post['ids'], ','); 
           }else{
             $this->error('请分配权限','shop/Admin/index');
           }
          
          if(input('post.accessType/d') == 1){ //添加
              if ($accessModel->allowField(true)->save($post)) {
                operateLog('分配权限(记录用户id)','admin_access',$id,$this->admin_id);
                  $this->success('分配权限成功','shop/Admin/index');
              } else {
                  $this->error('分配权限失败','shop/Admin/index');
              }
          }else{ //修改
              if ($accessModel->save(['ids'=>$post['ids']],['uid'=>$post['uid']]) !==false) {
                operateLog('修改权限(记录用户id)','admin_access',$id,$this->admin_id);
                  $this->success('修改权限成功','shop/Admin/index');
              } else {
                  $this->error('修改权限失败','shop/Admin/index');
              }
          }      
      }else{
           $lists = $accessModel->getAccess($this->admin_id,$this->admin_type,$id);
           $menu_lists  = array2tree($lists);
           $hasAccess = db('admin_access')->where('uid',$id)->value('ids');
           return $this->fetch('auth', ['menu_lists' => $menu_lists,'hasAccess' => $hasAccess]); 
      }
    }
	
}
