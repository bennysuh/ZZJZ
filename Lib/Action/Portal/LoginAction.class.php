<?php
// +----------------------------------------------------------------------
// |zzjz 
// +----------------------------------------------------------------------
// | Copyright (c) 2012 gzzzjz.com All rights reserved.
// +----------------------------------------------------------------------
// | Link 
// +----------------------------------------------------------------------
// | Author: david <david@gmail.com>
// +----------------------------------------------------------------------
// $Id: LoginAction.class.php	


class LoginAction extends Action {
	
	private $user;
	private $qqHelper;
	
	public function _initialize() {
		$this->user = M ( 'tbl_user' );
		import ( '@.ORG.QQHelper' );
		$this->qqHelper = new QQHelper ();
	}
	
	//显示登录页面
	public function index() {
		if (session ( '?id' )) {
			//没有登录跳转到登录页面
			$this->redirect ( '/Portal/Main/' );
		}
		$this->display ();
	}
	
	//处理普通登录请求 本站会员登录请求
	public function check() {
		$uname = $_POST ['uname'];
		$pwd = $_POST ['pwd'];
		
		$result = $this->user->where ( array ('uname' => $uname, 'pwd' => $pwd ) )->find ();
		if ($result != null) {
			session ( 'id', $result ['id'] );
			$this->success ( '登录成功！', U ( 'Main/' ) );
		} else {
			$this->error ( '帐号或密码错误！' );
		}
	}
	//注销
	public function gone() {
		foreach ($_SESSION as $k=>$v) {
			unset($_SESSION[$k]);
		}
		$this->redirect ( '/Portal/Login' );
	}
	
	//注册一个本站帐号
	public function regesiter(){
		if($this->isPost()){
			$data = $this->user->create(); 
			if($data){
				$isAlready = $this->user->where("uname=" . $data['uname'])->count();
				if($isAlready > 0){
					$this->error("已存在此用户名");
				}
				$this->user->bind='N';
				if($this->user->add()>0){
					$this->success('注册成功',__APP__);
				}else{
					$this->error('注册失败');
				}
			}else {
				$this->error($this->user->getError());
			}
		}else{
			$this->error('你懂的');
		}
	}
	
	//QQ登录
	public function qq_login() {
		$this->qqHelper->login ( APP_ID, APP_SCOPE, APP_LOGIN_CPATH );
	}
	
	//QQ登录的回调
	public function qq_login_cb() {
		$this->qqHelper->callback ( APP_LOGIN_CPATH );
		$this->qqHelper->get_openid ();
		
		//先去数据库中查找openid存在不
		$temp_data = $this->user->where ( array ('open_id' => $_SESSION [SES_OPENID_NAME] ) )->find ();
		Log::write($temp_data);
		if ($temp_data == null) {
			//证明该QQ还没有在本站登录过
			$userInfo = json_decode ( $this->qqHelper->get_user_info (), true );
			$data ['open_id'] = $_SESSION [SES_OPENID_NAME];
			$data ['nickname'] = $userInfo ['nickname'];
			$data ['avatar'] = $userInfo ['figureurl_2'];
			$data ['ip'] = get_client_ip ();
			dump($userInfo);
			$this->user->add ( $data );
			session ( 'id', $this->user->getLastInsID () );
		} else {
			session ( 'id', $temp_data ['id'] );
		}
		$this->redirect ( '/Portal/Main/' );
	}

}