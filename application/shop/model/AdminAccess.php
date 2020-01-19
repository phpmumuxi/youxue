<?php
namespace app\shop\model;

use \think\Model;

class AdminAccess extends Model
{
	/**
     * 获取权限列表数据
     * return []
     */
	public function getAccess($admin_id,$admin_type,$id){
         $schoolId=db('admin')->where('id',$id)->value('schoolId');
         if($schoolId){
             $info   =  db('menu')
                     -> field('id,name,pid')
                     -> where(['type'=>3])
                     -> where('id','not in','138,139,140,151') 
                     //138,139,140,151校区财务管理菜单屏蔽
                     -> order(['sort' => 'DESC', 'id' => 'ASC'])
                     -> select();
         }else{
    		 $ids=db('admin_access')->where('uid',$admin_id)->value('ids');
    	     $info   =  db('menu')
                     -> field('id,name,pid')
                     -> where(['type'=>$admin_type])
                     -> where('id','in',$ids)
                     -> order(['sort' => 'DESC', 'id' => 'ASC'])
                     -> select();
         }
          return $info;
	}

}