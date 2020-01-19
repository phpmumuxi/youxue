<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\Admin as AdminModel;
use app\admin\model\AdminAccess as AdminAccessModel;
use app\admin\model\AdminRole as AdminRoleModel;
use think\Config;

/**
 *  管理员列表管理
 * Class Manage
 * @package app\admin\controller
 */
class Admin extends AdminBase
{
	  protected $manage_model;

    protected function _initialize()
    {
        parent::_initialize();
        
        $this->manage_model = new AdminModel(); 
    }
    
    /**
	 * 管理员列表
	 * Class Manage
	 * @package app\admin\controller
	 */

    public function index()
    {
      $this->getAcessTypes();
      $admin_id=$this->admin_id;
      $userName = input('get.userName');
      $shopId = input('get.shopId');
      $schoolId = input('get.schoolId');
      $userName ? $this->assign('userName', $userName) : '';
      $data = $this->manage_model->getManages($admin_id,$userName,$shopId,$schoolId);
      $page = $data->render();  // 获取分页显示
      $info = $data->toArray();  
      $shops = $this->manage_model->getAdminShops();
      $schools = $this->manage_model->getAdminSchools();  
      return $this->fetch('index', ['info' => $info,'shops' => $shops,'schools' => $schools,'shopId' => $shopId,'schoolId' => $schoolId,'admin_id' => $admin_id,'page' => $page]);
    }


	/**
	 * 管理员添加
	 * Class Manage
	 * @package app\admin\controller
	 */
	public function add()
    {
      $admin_id=$this->admin_id;
		 if ($this->request->isPost()) {//数据提交并处理   	     
           $post=input('post.');           
           $post['name']=input('post.name');
           if($admin_id==1){
               $post['type']=input('post.type');
               if($post['type']==2){
                $post['roleId']=2;
               }elseif($post['type']==3){
                $post['roleId']=3;
               }
           }else{
              $post['type']=1;
           }
       $post['createTime']=time();
		   $post['adminId']=$this->admin_id;
       $post['password'] = md5(input('post.password') . Config::get('ValidationKey'));
       $info = $this->manage_model->validLogin(['name'=>$post['name'],'password'=>$post['password'],'isDelete'=>1]);               
		   if($info){
		      $this->error('用户已存在','admin/Admin/index');
		   }
		      if ($this->manage_model->allowField(true)->save($post)) {
              $adminId=$this->manage_model->id;
              $this->redirect('admin/Admin/auth',['id'=>$adminId]);
          } else {
              $this->error('保存失败','admin/Admin/add');
          }		   
		  }else{			//添加页面展示
             $role   = new AdminRoleModel();
             $roles  = $role->getAdminRoles();
             $shops   = $this->manage_model->getAdminShops();
             return $this->fetch('add', ['roles' => $roles,'shops' => $shops,'admin_id' => $admin_id]); 
      }
         
    }

    /**
     * 删除管理员
     * @param $id
     */
    public function del($id)
    {    	
        if ($this->manage_model->update(['isDelete'=>0,'id'=>$id])) {
            operateLog('删除管理员','admin',$id,$this->admin_id);
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }


    /**
     * 获取权限数据
     * 
     */
    public function auth($id)
    {
        if(!$id){
          $this->error('参数错误！');
        }
        $info = db('admin')->field('type,adminId')->where('id',$id)->find();
        if ($this->request->isPost()) {//数据提交并处理 
           $post['uid']  = $id;          
           $post['ids'] =  input('post.ids/a');
           if($post['ids']){
             $post['ids']=implode($post['ids'], ','); 
           }else{
             $this->error('请分配权限','admin/Admin/index');
           }
          $accessModel = new AdminAccessModel();
          if(input('post.accessType/d') == 1){ //添加
              if ($accessModel->allowField(true)->save($post)) {
                  operateLog('分配权限(记录用户id)','admin_access',$id,$this->admin_id);
                  $this->success('分配权限成功','admin/Admin/index');
              } else {
                  $this->error('分配权限失败','admin/Admin/index');
              }
          }else{ //修改
              if ($accessModel->save(['ids'=>$post['ids']],['uid'=>$post['uid']]) !==false) {
                  operateLog('修改权限(记录用户id)','admin_access',$id,$this->admin_id);
                  $this->success('修改权限成功','admin/Admin/index');
              } else {
                  $this->error('修改权限失败','admin/Admin/index');
              }
          }      
      }else{      //页面展示
        if($this->admin_id ==1 || $info['type']== 4){          
           $menu_lists    = $this->getAccess($this->admin_id,$info['type']);
        }else{
          $ids=db('admin_access')->where('uid',$info['adminId'])->value('ids');
          if(!$ids){
            $this->error('请先给该登陆者分配权限！');
          }
          $menu_lists    = $this->getAccess($info['adminId'],$ids);
        }
           $hasAccess = db('admin_access')->where('uid',$id)->value('ids');
           return $this->fetch('auth', ['menu_lists' => $menu_lists,'hasAccess' => $hasAccess]); 
      }
    }

    /**
     * 获取权限列表数据
     * 
     */
    public function getAccess($dminId,$wheres)
    {
      if($dminId==1){
        $where['type']=$wheres;
      }else{
        $where['id']=['in',$wheres];
      }
        $menu_lists  =  db('menu')
                     -> field('id,name,pid')
                     -> where($where)
                     -> where('id','not in','5,6,7,8,138,139,140,151')//系统菜单不显示//138,139,140,151校区财务暂时屏蔽
                     -> order(['sort' => 'DESC', 'id' => 'ASC'])
                     -> select();
        $menu_lists  = array2tree($menu_lists);
        return $menu_lists;
    }

    // 获取校区数据
    public function getSchools()
    {
      $id=input('get.id/d');
      $lists  =  db('school')
              -> field('id,name')
              -> where(['status'=>1,'shopId'=>$id])
              -> select();              
       $str = "<option value=''>--请选择--</option>";
      foreach ($lists as $val) {
          $str .= "<option value='".$val['id']."'>".$val['name']."</option>";        
      }
      echo $str;
    }
	 
   //修改手机号
   public function updatePhone()
    {
      $arr=input('post.');
      if(db('admin')->where('id',$arr['id'])->setField('phone',$arr['phone'])){
          return ['status'=>'ok','msg'=>'手机号更新成功!'];
      }else{
          return ['status'=>'ok','msg'=>'手机号更新失败!'];
      }
    }

}
