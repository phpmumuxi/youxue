<?php
namespace app\common\controller;

use think\Controller;
use think\Session;
use think\Db;
use app\admin\model\Menu as MenuModel;

/**
 * 后台公用基础控制器
 * Class AdminBase
 * @package app\common\controller
 */
class AdminBase extends Controller
{
    protected $admin_id;
    protected $admin_type;
    protected function _initialize()
    {
        parent::_initialize();

        if (!Session::has('admin_id')) {
            echo "<script>window.top.location.href = '/admin/Login/index'</script>";            
            exit();
        }

        //判断登陆会话是否过期
        if (time() - Session::get('admin_start_time') > config('admin_session_expire')) {
            //真正的销毁在这里！
            Session::delete('admin_id');
            Session::delete('admin_name');
            Session::delete('admin_type');
            Session::delete('admin_start_time');
            echo "<script>window.top.location.href = '/admin/Login/index'</script>";            
            exit();
        }else{
            Session::set('admin_start_time', time());// 刷新时间戳
        }

        $this->admin_id = Session::get('admin_id');
        $this->admin_type = Session::get('admin_type');

        $ids =db('admin_access')->where('uid',$this->admin_id)->value('ids');
        $this->checkAuth($ids);
        $this->getMenus($ids);
    }

    /**
     * 权限检查
     * @return bool
     */
    protected function checkAuth($ids)
    {
        $module     = $this->request->module();
        $controller = $this->request->controller();
        $action     = $this->request->action();
        $url = $module . '/' . $controller . '/' . $action;
        $menuid = db('menu')->where('url',$url)->value('id');

        if($menuid){
			if (strpos($ids,"$menuid") === false && $this->admin_id !=1) {
                $this->redirect('admin/Login/access');
            }
        }
    }

    /**
     * 获取侧边栏菜单
     */
    protected function getMenus($ids)
    {
        $menus= new MenuModel;
        $lists = $menus->getLists($this->admin_id,$this->admin_type,$ids);
        $menus = !empty($lists) ? array2tree($lists) : [];
        $this->assign('menus', $menus);
    }

    /**
     * 权限的操作类型判断
     */
    protected function getAcessTypes()
    {
        if($this->admin_id==1){
            $this->assign('actionType', [0,1,2,3,4,5,6,7]);
        }else{
            $module     = $this->request->module();
            $controller = $this->request->controller();
            $url = $module . '/' . $controller . '/' ;
            $menus= new MenuModel;
            $actionType = $menus->getAcessTypes($url,$this->admin_type,$this->admin_id);
            $this->assign('actionType', $actionType);
        }
        
    }
    
}