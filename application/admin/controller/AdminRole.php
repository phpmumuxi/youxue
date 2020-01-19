<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\AdminRole as AdminRoleModel;

/**
 *  角色管理
 * @package app\admin\controller
 */
class AdminRole extends AdminBase
{
	protected $role_model;

    protected function _initialize()
    {
        parent::_initialize();        
        $this->role_model = new AdminRoleModel(); 
    }
    
    /**
	 * 角色列表
	 * @package app\admin\controller
	 */

    public function index()
    {
      
        //本类型角色列表        
	  $roleLists = $this->role_model
               -> getRoles();
	  return $this->fetch('index', ['roleLists' => $roleLists]);	  
        
    }


	/**
	 * 角色添加
	 * @package app\admin\controller
	 */
	public function add()
    {
		 if ($this->request->isPost()) {//数据提交并处理   	       
            $post       = input('post.');          
            $post['type']  = 1;		   
		   if ($this->role_model->allowField(true)->save($post)) {
                $this->success('保存成功','admin/AdminRole/index');
            } else {
                $this->error('保存失败','admin/AdminRole/add');
            }		   
		 }else{			//添加页面展示
         return $this->fetch();
		 }
		 
    }

    /**
     * 编辑角色
     * @param $id
     * 
     */
    public function edit($id)
    {
    	if ($this->request->isPost()) {
            $data = $this->request->post();			
            if ($this->role_model->save($data, ['id'=>$id]) !== false) {
                operateLog('编辑角色','admin_role',$id,$this->admin_id);
                $this->success('更新成功','admin/AdminRole/index');
            } else {
                $this->error('更新失败','admin/AdminRole/index');
            }            
        }else{        	
	        $info = $this->role_model->find($id)->toArray();
	        return $this->fetch('edit', ['info' => $info]);
        }
    }

    /**
     * 删除角色
     * @param $id
     */
    public function del($id)
    {    	
        if ($this->role_model->destroy($id)) {
            operateLog('删除角色','admin_role',$id,$this->admin_id);
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

}
