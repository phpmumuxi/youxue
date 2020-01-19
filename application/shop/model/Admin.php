<?php
namespace app\shop\model;

use \think\Model;

class Admin extends Model
{
	//商户后台管理员列表
	public function getShopsAdmin($admin_id,$schoolId){

		  $where=$schoolId?['m.schoolId'=>$schoolId]:'';
		  $shopid = db('admin') ->field('shopId')-> find($admin_id);
	      $info = db('admin')
	           -> alias('m')
	           //'m.adminId'=>$admin_id 创建的人是否显示
	           -> where(['m.isDelete'=>1,'m.adminId'=>$admin_id])
	           -> where('m.shopId',$shopid['shopId'])
	           ->where($where)
	           -> field('m.id,m.name,m.phone,r.roleName,m.createTime,sc.name as schoolName')
	           -> join('t_admin_role r','r.id = m.roleId','LEFT')
	           // -> join('t_shop s','s.id = m.shopId','LEFT')
	           -> join('t_school sc','sc.id = m.schoolId','LEFT')
	           ->paginate(10, false, ['query' => ['schoolId' => $schoolId]]); 
	      return $info;
	}

	//该商户下的所有校区
    public function getAdminSchools($shopId){
            $schools  =  db('school')
                      -> field('id,name')
                      -> where('status!=3')//不删除状态
                      -> where(['shopId'=>$shopId])
                      -> select();
            return $schools;
    }
}