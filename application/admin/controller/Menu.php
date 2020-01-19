<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\Menu as MenuModel;

/**
 * 菜单管理
 * Class Menu
 * @package app\admin\controller
 */
class Menu extends AdminBase
{

	protected $menu_model;

    protected function _initialize()
    {
        parent::_initialize();
        $this->menu_model = new MenuModel();        
    }
    
    /**
	 * 菜单展示
	 * Class Menu
	 * @package app\admin\controller
	 */

    public function index()
    {
	  
	  $menu_lists  = $this->menu_model->getMenus();
	  $menu_lists  = array2level($menu_lists);					      
	  return $this->fetch('index',['menu_lists'=>$menu_lists]);
    }


	/**
	 * 菜单添加
	 * Class Menu
	 * @package app\admin\controller
	 */
	public function add()
    {
         $id=input('?param.id')?input('param.id/d'):'0';
         $kot=input('?param.kot')?input('param.kot/d'):'0';
		 $doType=input('param.doType/d');
		 if ($this->request->isPost()) {	//数据提交并处理
		   if($id==0 && $kot==1){    //总菜单
			   $post=input('post.');
			   $post['pid']=0;
			   $post['level']=0;
			    if ($this->menu_model->allowField(true)->save($post)) {
                    $this->success('保存成功','admin/Menu/index');
                } else {
                    $this->error('保存失败','admin/Menu/index');
                }
		   }else{  //单一菜单栏下数据提交并处理
		      $res=$this->menu_model->find($id); //->where('id',$id )
               $post=input('post.');
			   $post['pid']=$res->id;
			   $post['type']=$res->type;
			   $post['level']=$res->level + 1;
			   if ($this->menu_model->allowField(true)->save($post)) {
                    $this->success('保存成功','admin/Menu/index');
                } else {
                    $this->error('保存失败','admin/Menu/index');
                }
		   }
		 }else{			//添加页面展示
			return $this->fetch('add',['kot'=>$kot,'doType'=>$doType]);
		 }
    }

    /**
     * 编辑菜单
     * @param $id
     */
    public function edit($id)
    {
        $doType=input('param.doType/d');
    	if ($this->request->isPost()) {
            $data   = $this->request->post();			
            if ($this->menu_model->save($data, ['id'=>$id]) !== false) {
                operateLog('编辑菜单','menu',$id,$this->admin_id);
                $this->success('更新成功','admin/Menu/index');
            } else {
                $this->error('更新失败','admin/Menu/index');
            }            
        }else{        	
	        $menu = $this->menu_model->find($id);         
	        return $this->fetch('edit', ['menu' => $menu,'doType' => $doType]);
        }
    }

    /**
     * 删除菜单
     * @param $id
     */
    public function del($id)
    {    	
        if ($this->menu_model->destroy($id)) {
            operateLog('删除菜单','menu',$id,$this->admin_id);
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
	
}
