<?php
namespace app\admin\controller;

use think\Controller;
use think\Config;
use app\admin\model\Admin as AdminModel;
use think\Session;
/**wyl
 * 后台登录
 * Class Login
 * @package app\admin\controller
 */
class Login extends Controller
{
	/**wyl
	 * 后台登录主页面
	 * 
	 */
    public function index()
    {
		return $this->fetch();
    }

    /**wyl
     * 登录验证
     * 
     */
    public function login()
    {
        if ($this->request->isPost()) {
            $data   = $this->request->only(['name', 'password']);
            $where['name'] = $data['name'];
            $where['password'] = md5($data['password'] .Config::get('validationKey'));
            $where['isDelete'] =1;
            $admin_model = new AdminModel;
            $admin_user =$admin_model->validLogin($where);
            if (!empty($admin_user)) { 
                if($admin_user['shopId']>0 && $admin_user['schoolId']==0){
                   $shop=db('shop')->where('status','in','0,1')->where(['id'=>$admin_user['shopId']])->find();
                   if(!$shop){
                        $this->error('已下架或删除，不允许登陆！','admin/Login/index');
                   }
                }elseif($admin_user['shopId']>0 && $admin_user['schoolId']>0){
                    $school=db('school')->where('status','in','0,1')->where(['id'=>$admin_user['schoolId']])->find();
                    if(!$school){
                        $this->error('已下架或删除，不允许登陆！','admin/Login/index');
                    }
                }else{
                  
                }                
                    Session::set('admin_start_time', time());//记录会话开始时间！           
                    Session::set('admin_id', $admin_user['id']);
                    Session::set('admin_name', $admin_user['name']);
                    Session::set('admin_type', $admin_user['type']);  
                    $this->redirect('admin/index/index');               
            } else {
                $this->error('用户名或密码错误');
            }
        }
    }

    /**
     * 管理员密码修改
     * 
     */
    public function passEdit()
    {
        if ($this->request->isPost()) {
            $where['name'] =  input('post.name');
            $where['password'] =  md5(input('post.password') . Config::get('ValidationKey'));
            $password =  md5(input('post.password2') . Config::get('ValidationKey'));
            $admin_model = new AdminModel;
            $admin_user =$admin_model->validLogin($where);
            if($admin_user){
                $user_id = $admin_user['id'];
            }else{
                $user_id = 0;
            }
            $admin_id = Session::get('admin_id');
            if($user_id != $admin_id){
               return ['msg'=>'密码错误','code'=>0]; 
            }else{               
                if ((db('admin')->where('id',$admin_id)->setField('password',$password)) !== false) {
                    return ['msg'=>'密码修改成功','code'=>1];
                } else {
                    return ['msg'=>'密码修改失败','code'=>2];
                }            
            }         
        }else{  
            return $this->fetch();
        }
    }

    /**wyl
     * 退出登录
     */
    public function logout()
    {
        Session::delete('admin_id');
        Session::delete('admin_name');
        Session::delete('admin_type');
        $this->redirect('admin/login/index');
    }

    /**wyl
     * 无权限错误页面
     * 
     */
    public function access()
    {
        return $this->fetch();
    }
}
