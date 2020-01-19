<?php
namespace app\admin\model;

use \think\Model;
use think\Db;

class Admin extends Model
{
	//验证登陆名是否存在
  public function validLogin($where){
      $res=Db::name('admin')->field('id,name,type,shopId,schoolId')->where($where)->find();
      return !empty($res) ? $res : [];
  }

  //获取管理员列表
  public function getManages($id,$userName,$shopId,$schoolId){
    $where = $userName ? ['m.name' => ['like', "%$userName%"]] : '';
    if($shopId){
      $where1 = 'm.shopId='.$shopId;
    }else{
      $where1 = '';
    }
    if($schoolId){
      $where2 = 'm.schoolId='.$schoolId;
    }else{
      $where2 = '';
    }
  	if($id !=1){
    		$info =  Db::name('admin')
              -> alias('m')
              -> field('m.id,m.name,m.phone,r.roleName,m.createTime')
              -> where(['m.isDelete'=>1,'m.adminId'=>$id])
              ->where($where)
              -> join('t_admin_role r','r.id = m.roleId','LEFT')
              -> paginate(10, false, ['query' => [
                'userName' => $userName]]);
  	}else{

	  	$info =  Db::name('admin')
	           -> alias('m')
	           -> where(['m.isDelete'=>1])
	           -> field('m.id,m.name,m.phone,r.roleName,m.createTime,s.name as shopName,sc.name as schoolName')
             ->where($where)
             ->where($where1)
             ->where($where2)
	           -> join('t_admin_role r','r.id = m.roleId','LEFT')
	           -> join('t_shop s','s.id = m.shopId','LEFT')
	           -> join('t_school sc','sc.id = m.schoolId','LEFT')
	           -> paginate(10, false, ['query' => [
                'userName' => $userName,'shopId' => $shopId,'schoolId' => $schoolId]]);
  	}
    return !empty($info) ? $info : [];
  }

  //获取商户列表
  public function getAdminShops(){
      $shops  =  db('shop')
                  -> field('id,name')
                  -> where('status!=3')
                  -> select();
      return $shops;
  }

  //获取校区列表
  public function getAdminSchools(){
      $schools  =  db('school')
                -> field('id,name')
                -> where('status!=3')
                -> select();
      return $schools;
  }

}