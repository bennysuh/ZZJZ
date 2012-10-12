<?php
class MainAction extends Action {
	private $user;
	private $qqHelper;
	
	public function _initialize() {
		$this->user = M ( 'tbl_user' );
		import ( '@.ORG.QQHelper' );
		$this->qqHelper = new QQHelper ();
	}
	
	public function index() {
		if (! session ( '?id' )) {
			//没有登录跳转到登录页面
			$this->redirect ( '/Portal/Login/index' );
		}
		
		$result = $this->user->where ( array ('id' => session ( 'id' ) ) )->find ();
		$this->assign ( 'user', $result );
		//显示登录页面
		$this->display ();
	}
	
	//绑定用户名 指采用QQ登录的用户，还没有本站帐号和密码 
	public function bind_uname() {
		$data ['uname'] = $_POST ['uname'];
		$data ['pwd'] = $_POST ['pwd'];
		$data ['bind'] = 'Y';
		if(!$_SESSION[SES_OPENID_NAME]){
			$this->error("尚未使用QQ登录");
		}
		$map ['open_id'] = $_SESSION [SES_OPENID_NAME]; //QQ的openid
		//查找该openid是否已经绑定过用户名没有
		$stat = $this->user->where ( $map )->find ();
		if ($stat ['uname'] != '' || $stat ['uname'] != null) {
			$this->error ( '你已经绑定过用户名，请不要再绑定啦' );
		}
		
		$res = $this->user->where ( $map )->save ( $data );
		if ($res > 0) {
			$this->success ( '绑定用户成功', __APP__ );
		} else {
			$this->error ( '绑定失败' );
		}
	}
	
	//绑定QQ号 主要是通过本站注册的用户
	public function qq_bind() {
		//先去判断该帐号是否绑定过QQ号
		$map ['open_id'] = $_SESSION [SES_OPENID_NAME];
		$res = $this->user->where ( $map )->find ();
		if ($res != null) {
			$this->error ( '您已经绑定了QQ帐号了，如果要更改请先解除绑定！' );
		}
		$this->qqHelper->login ( APP_ID, APP_SCOPE, APP_BIND_CPATH ); //注意这里的回调地址和登录的回调地址不同
	}
	
	//绑定QQ号 的回调地址
	public function qq_bc() {
		$tmp_ses = $_SESSION [SES_OPENID_NAME]; //保存之前的Sesion 避免Session被覆盖
		$this->qqHelper->callback ( APP_BIND_CPATH ); //注意这里的回调地址和登录的回调地址不同
		$this->qqHelper->get_openid ();
		$userInfo = json_decode ( $this->qqHelper->get_user_info (), true );
		
		//先要去判断这个QQ号是否已经绑定过帐号没有
		$res = $this->user->where ( array ('open_id' => $_SESSION [SES_OPENID_NAME] ) )->find ();
		if ($res != null) {
			$_SESSION [SES_OPENID_NAME] = $tmp_ses;
			$this->error ( '该QQ号已经绑定过帐号了，请勿再次绑定', __APP__ );
		}
		$map ['id'] = session ( 'id' );
		$data ['open_id'] = $_SESSION [SES_OPENID_NAME];
		$data ['avatar'] = $userInfo ['figureurl_2'];
		$data ['bind'] = 'Y';
		$stat = $this->user->where ( $map )->save ( $data );
		if ($stat > 0) {
			$this->success ( '绑定QQ号成功！', __APP__ );
		} else {
			$this->error ( '绑定QQ失败', __APP__ );
		}
	}
	
	//解绑QQ号
	public function qq_unbind() {
		$map ['id'] = session ( 'id' );
		$data ['open_id'] = '';
		$data ['bind'] = 'N';
		$res = $this->user->where ( $map )->save ( $data );
		if ($res > 0) {
			$this->success ( '解绑成功！', __APP__ );
		} else {
			$this->error ( '解绑失败', __APP__ );
		}
	}
	
	//读模板
	public function readtemplate() {
		$dotpl = $_GET ['source'];
		
		if ($dotpl == 'bind_uname') {
			$this->display ( 'bind_uname' );
		}
	}

}