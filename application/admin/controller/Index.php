<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;

class Index extends AdminBase
{
	//首页入口
    public function index()
    {
		return $this->fetch();
    }
	
	//网站概述首页
	public function welcome()
    {
       
		return $this->fetch();
    }
    
}
