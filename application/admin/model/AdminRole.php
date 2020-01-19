<?php
namespace app\admin\model;

use \think\Model;
use \think\Db;

class AdminRole extends Model
{

	//角色列表数据获取
   public function getRoles(){
      $roleLists = Db::name('admin_role')
               -> select();
      return !empty($roleLists) ? $roleLists : [];
   }

   /**
     * 获取后台角色数据
     * 
     */
    public function getAdminRoles()
    {
          $roles  = Db::name('admin_role')
                  -> field('id,roleName')
                  -> where(['type'=>1])
                  -> select();
          return !empty($roles) ? $roles : [];
    }
}