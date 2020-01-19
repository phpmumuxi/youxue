<?php
namespace app\school\controller;

use app\common\controller\AdminBase;
use app\common\model\SchoolCommon;
use app\school\model\Adviser as AdviserModel;

/**
 *  顾问列表管理
 * Class Adviser
 * @package app\school\controller
 */
class Adviser extends AdminBase
{

  /**
	 * 校区后台顾问列表
	 * Class Adviser
	 * @package app\school\controller
	 */

    public function index()
    {
      $phone = input('get.phone');              
      $phone ? $this->assign('phone', $phone) : '';

      $schoolId = $this->getAdminschoolId();
      $adviserModel = new AdviserModel();
      $data = $adviserModel-> getAdviserDatas($schoolId,$phone); 
      $page = $data->render();  
      $lists = $data->toArray();     
      return $this->fetch('index', ['lists' => $lists,'page' => $page]);
    }


	/**
	 * 顾问添加
	 * Class Adviser
	 * @package app\school\controller
	 */
	public function add()
    {
		 if ($this->request->isPost()) {//数据提交并处理   	       
           $post=input('post.');
           $adviserModel = new AdviserModel();
           $res = $adviserModel-> hasAdviserOrUser($post,$this->admin_id);
           if($res=='has'){
                return ['status'=>'has','msg'=>'该顾问已存在！'];
           }elseif($res=='isBenefit'){
                return ['status'=>'isBenefit','msg'=>'该用户已经是受益人，不能添加为顾问！'];           
           }elseif($res=='ok'){
                return ['status'=>'ok','msg'=>'添加成功！'];
           }else{
                return ['status'=>'err','msg'=>'添加失败！'];
           }
           	   
		  }else{
            return $this->fetch(); 
      }
         
    }

    /**
   * 顾问编辑
   * Class Adviser
   * @package app\school\controller
   */
  public function edit()
    {
     if ($this->request->isPost()) {         
           $data   = input('post.');
           $adviserModel = new AdviserModel();
           $res = $adviserModel-> editAdviserInfos($data,$this->admin_id);
           if ($res=='no') {                
                return ['status'=>'no','msg'=>'请先在APP上注册为用户！'];
            }elseif($res=='isBenefit'){
                return ['status'=>'isBenefit','msg'=>'该用户已经是受益人，不能添加为顾问！'];
            }elseif($res=='has'){
                return ['status'=>'has','msg'=>'该顾问已存在！'];
            }elseif($res=='changePhone'){
                operateLog('编辑顾问手机号','adviser_name',$data['id'],$this->admin_id);
                return ['status'=>'changePhone','msg'=>'手机号更新成功！'];
            }elseif ($res=='ok') {
                operateLog('编辑顾问','adviser_name',$data['id'],$this->admin_id);                
                return ['status'=>'ok','msg'=>'更新顾问成功！'];
            } else {                
                return ['status'=>'err','msg'=>'更新失败!'];
            }   
      }else{
          $id=input('param.id');
          $info=db('adviser_name')->find($id);
          return $this->fetch('edit', ['info' => $info]); 
      }
         
    }

    /**
     * 删除顾问
     * @param $id
     */
    public function del()
    {
        $id=input('post.id');
        $adviserModel = new AdviserModel();
        $res = $adviserModel-> adviserHasUser($id);
        if($res=='has'){
              return ['status'=>'has','msg'=>'该顾问下有用户，不允许删除！'];
         }elseif($res=='ok'){
              operateLog('删除顾问','adviser_name',$id,$this->admin_id);
              return ['status'=>'ok','msg'=>'删除成功！'];
         }else{
              return ['status'=>'err','msg'=>'删除失败！'];
         }        
    }

    //分配顾问管理     
    public function distribute()
    {
      $phone = input('get.phone');              
      $phone ? $this->assign('phone', $phone) : '';

      $schoolId = $this->getAdminschoolId();
      $adviserModel = new AdviserModel();
      $data = $adviserModel-> getAdviserDatas($schoolId,$phone); 
      $page = $data->render();  
      $lists = $data->toArray();     
      return $this->fetch('distribute', ['lists' => $lists,'page' => $page]);
    }

    // 顾问名下客户    
    public function adviserDownUsers()
    {     
        $id=input('param.id');
        $phone = input('get.phone');              
        $phone ? $this->assign('phone', $phone) : '';  
        $adviserModel = new AdviserModel();
        $schoolId = $this->getAdminschoolId();
        $advises = $adviserModel-> getadvisers($schoolId);
        $data = $adviserModel-> adviserDownUsers($id,$phone);
        $page = $data->render();
        $lists = $data->toArray();
        return $this->fetch('adviserDownUsers', ['id' => $id,'advises' => $advises,'lists' => $lists,'page' => $page]);        
    }

    //顾问销售额管理
    public function adviserMoney()
    { 
      $date = [            
        'sTime'=>input('get.sTime',date('Y-m-d', strtotime('-7 days'))),
        'eTime'=>input('get.eTime',date('Y-m-d', strtotime('-1 days')))        
        ];
      $schoolId = $this->getAdminschoolId();
      $adviser_model = new AdviserModel();      
      $datas = $adviser_model->getAdviserMoneyLists($schoolId,$date);
      $page = $datas->render();
      $lists = $adviser_model->getAdviserMoneyDatas($datas->toArray(),$date,$schoolId);
      return $this->fetch('adviserMoney', ['date' => $date,'lists' => $lists,'page' => $page]);
    }

    //顾问销售额账单详情  
    public function adviserMoneyBill()
    {
       $arr=input('param.');
       //$arr['schoolId'] = $this->getAdminschoolId();
       $adviser_model = new AdviserModel();

       //优选课程
       $datas = $adviser_model->getBillMoneyDetail($arr,1);
       $page = $datas->render();  
       $lists = $datas->toArray();
       //万人砍课程 
       $infos = $adviser_model->getBillMoneyDetail($arr,2);
       $page1 = $infos->render();  
       $lists1 = $infos->toArray();           
       return $this->fetch('adviserMoneyBill', ['lists'=>$lists,'page'=>$page,'lists1'=>$lists1,'page1'=>$page1]);
    }

    // 获取校区Id     
    public function getAdminschoolId()
    {
        $SchoolCommonModel = new SchoolCommon();
        $schoolId = $SchoolCommonModel->getSchoolIdFromAdminId($this->admin_id);
        if (!$schoolId) {
            $this->error('该管理员没有指定校区!');
        }
        return $schoolId;
    }

    /**
     * 重新分配顾问Ajax
     * @param 
     */
    public function distributeAjax()
    {
        $adviserId=input('post.adviserId');
        $oldAdviserId=input('post.oldAdviserId');
        $ids=input('post.ids');
        $remark=input('post.remark');
        $adviserModel = new AdviserModel();
        $schoolId = $this->getAdminschoolId();
        $res = $adviserModel-> distributeAdviser($ids,$adviserId,$schoolId,$remark,$this->admin_id,$oldAdviserId);
        if($res){ 
            operateLog('重新分配顾问','adviser_user_school',0,$this->admin_id);
            return ['status'=>'ok','msg'=>'分配成功！'];
        }else{
            return ['status'=>'err','msg'=>'分配失败！'];
        }
    }

    // 重新分配顾问日志     
    public function adviserLogs()
    {
        $adviserId = 0;
        if (input('?get.adviserId')) {
            $adviserId = input('get.adviserId');
        }
        $adviserModel = new AdviserModel();
        $schoolId = $this->getAdminschoolId();
        $advises = $adviserModel->getadvisers($schoolId);
        $data = $adviserModel->getAdviserLogs($schoolId,$adviserId);
        $page = $data->render();
        $lists = $data->toArray();
        return $this->fetch('adviserLogs', ['adviserId' => $adviserId,'advises' => $advises,'lists' => $lists,'page' => $page]);
    }

}
