<?php
namespace app\admin\model;

use think\Model;
use think\Db;

class Menu extends Model
{
  //基类不同类型的菜单获取
   public function getLists($id,$type,$ids){
   		if($id==1){
           $where='';
   		}else{
			     $where['id'] = ['in', $ids];
   		}
   		 $lists  =  Db::name('menu')
                     -> field('id,url,pid,name,icon')
                     -> where(['status' => 1,'type' =>$type])//1为菜单显示状态
                     -> where($where)
                     ->order(['sort' => 'DESC', 'id' => 'ASC'])
                     -> select();
        return !empty($lists) ? $lists : [];
   }

   //菜单列表数据获取
   public function getMenus(){
      $menu_lists  = Db::name('menu')
                 ->order(['sort' => 'DESC', 'id' => 'ASC'])
                 ->select();
      return !empty($menu_lists) ? $menu_lists : [];
   }

   //权限的操作类型判断
   public function getAcessTypes($url,$type,$id){
      $menuIds  = Db::name('menu')
                  ->field('id,actionType')
                  ->where('url','like',$url.'%')
                  ->where(['type'=>$type])
                 ->select();
     $accessIds  = Db::name('admin_access')
                ->where('uid',$id)
                ->value('ids');
     $accessIds  =explode(',', $accessIds);
     $actionType=[];
      foreach ($menuIds as $key => $v) {
              if(in_array($v['id'], $accessIds)){
                  $actionType[]= $v['actionType'];
              }
           } 
     // $actionType=rtrim($actionType,',');
      return !empty($actionType) ? $actionType : [];
   }
}