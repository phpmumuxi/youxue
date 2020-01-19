<?php
namespace app\school\model;

use \think\Model;
use \think\Db;

class Admin extends Model
{
	//校区后台管理员列表
	public function getSchoolAdmin($admin_id,$admin_type){

		  $schoolid = Db::name('admin') ->field('schoolId')-> find($admin_id);
	      $info = Db::name('admin')
	      		->alias('m')
	           -> where(['m.isDelete'=>1,'m.adminId'=>$admin_id,'m.type'=>$admin_type,'m.schoolId'=>$schoolid['schoolId']])
	           -> field('m.id,m.name,m.phone,r.roleName,m.createTime,s.name as schoolName')
	           -> join('t_admin_role r','r.id = m.roleId','LEFT')
	           -> join('t_shop s','s.id = m.shopId','LEFT')
	           -> select();
	      return $info;
	}
}