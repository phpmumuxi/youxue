<?php
namespace app\school\controller;

use app\common\controller\AdminBase;
use app\school\model\Admin as AdminModel;
use app\school\model\AdminAccess as AdminAccessModel;
use think\Config;

/**
 *  管理员列表管理
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
	 * 校区后台管理员列表
	 * Class Admin
	 * @package app\admin\controller
	 */

    public function index()
    {
      $info =$this->admin_model->getSchoolAdmin($this->admin_id,$this->admin_type);          
      return $this->fetch('index', ['info' => $info]);
    }


	/**
	 * 管理员添加
	 * Class Admin
	 * @package app\admin\controller
	 */
	public function add()
    {
		 if ($this->request->isPost()) {//数据提交并处理   	       
           $post=input('post.');           
           $post['name']=input('post.name');
           $post['createTime']=time();
		       $post['adminId']=$this->admin_id;
           $post['password'] = md5(input('post.password') . Config::get('ValidationKey'));
           $info = db('admin')
                ->where(['name'=>$post['name'],'password'=>$post['password'],'isDelete'=>1])
                ->find();
    		   if($info){
    		      $this->error('用户已存在','school/Admin/index');
    		   }
           $infos = db('admin') ->field('roleId,type,shopId,schoolId')-> find($this->admin_id);
           $post['type'] = $infos['type'];
           $post['shopId'] = $infos['shopId'];
           $post['schoolId'] = $infos['schoolId'];
           $post['roleId'] = $infos['roleId'];
		      if ($this->admin_model->allowField(true)->save($post)) {
              $adminId=$this->admin_model->id;
              $this->redirect('school/Admin/auth',['id'=>$adminId]);
          } else {
              $this->error('保存失败','school/Admin/add');
          }		   
		  }else{			//添加页面展示
             return $this->fetch(); 
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
             $this->error('请分配权限','school/Admin/index');
           }          
          if(input('post.accessType/d') == 1){ //添加
              if ($accessModel->allowField(true)->save($post)) {
                operateLog('分配权限(记录用户id)','admin_access',$id,$this->admin_id);
                  $this->success('分配权限成功','school/Admin/index');
              } else {
                  $this->error('分配权限失败','school/Admin/index');
              }
          }else{ //修改
              if ($accessModel->save(['ids'=>$post['ids']],['uid'=>$post['uid']]) !==false) {
                operateLog('修改权限(记录用户id)','admin_access',$id,$this->admin_id);
                  $this->success('修改权限成功','school/Admin/index');
              } else {
                  $this->error('修改权限失败','school/Admin/index');
              }
          }      
      }else{      //页面展示
           $lists = $accessModel->getAccess($this->admin_id,$this->admin_type);
           $menu_lists = array2tree($lists);
           $hasAccess = db('admin_access')->where('uid',$id)->value('ids');
           return $this->fetch('auth', ['menu_lists' => $menu_lists,'hasAccess' => $hasAccess]); 
      }
    }

    /**
     * 获取权限列表数据
     * return []
     */
    public function getAccess($admin_ids)
    {
        $menu_lists  =  db('menu')
                     -> field('id,name,pid')
                     -> where(['type'=>$this->admin_type])
                     -> where('id','in',$admin_ids)
                     -> order(['sort' => 'DESC', 'id' => 'ASC'])
                     -> select();
        $menu_lists  = array2tree($menu_lists);
        return $menu_lists;
    }
	
}
