<?php
namespace app\school\model;

use \think\Model;
use \think\Db;

class AdminAccess extends Model
{
	/**
     * 获取权限列表数据
     * return []
     */
	public function getAccess($admin_id,$admin_type){
		 $ids=Db::name('admin_access')->where('uid',$admin_id)->value('ids');
	     $info   =  Db::name('menu')
                 -> field('id,name,pid')
                 -> where(['type'=>$admin_type])
                 -> where('id','in',$ids)
                 -> where('id','not in','138,139,140,151') 
                     //138,139,140,151校区财务管理菜单屏蔽
                 ->order('sort desc')
                 -> select();
                 // halt($info );
          return $info;
	}
}