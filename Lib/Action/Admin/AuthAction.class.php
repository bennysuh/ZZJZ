<?php
// +----------------------------------------------------------------------
// | Elibrary [ ENJOY LIFE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://elibrary.nmg.com.hk All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: ccxopen <ccxopen@gmail.com>
// +----------------------------------------------------------------------
// $Id$

/**
 +------------------------------------------------------------------------------
 * 權限登錄，驗證用戶的身份
 +------------------------------------------------------------------------------
 * @author    ccxopen <ccxopen@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class AuthAction extends EntryAction
{
    
    public function index()
    {		
		$this->display();
	}

	/**
     +----------------------------------------------------------
     * 檢查是否有權限進入某個URL
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function checkAuthByUrl()
	{
		$url = $_POST['url'];
		$result = checkAuthByUrl($url);//调用common的方法

		if(!$_SESSION[C('ADMIN_AUTH_KEY')] && !$_SESSION[C('USER_AUTH_KEY')]) $result = "expiry";

		$this->success($result);
	}
    
    /**
     +----------------------------------------------------------
     * 登錄
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function login()
    {
		// 是否有驗證碼
        if (md5($_POST['verifyCode']) != $_SESSION['verify'] && C('VERIFY_CODE_MODE'))
			$this->error('驗證碼錯誤');//默认跳转回上一页

		$username = trim($_POST['username']);
		$password = trim($_POST['password']);
		//查询用户
		$user = M('user')->where("userID='$username' and status=1")->find();
		if (!$user) {
			$this->error("用戶名不存在");
		}
		if ($user['source'] == 'local') {//本地用户
			if ($user['password'] == $password || $user['password'] == md5($password)) {
				$_SESSION[C('USER_AUTH_KEY')] = $username;
			} else {
				$this->error("密碼錯誤");
			}
		} else {//EDB用户
			import("@.ORG.NmpsSoapClient");

			$NmpsSoapClient = new NmpsSoapClient();
			$params->loginID = $username;
			$params->password = $password;
			$result = $NmpsSoapClient->loginUser($params);
			$result = $result[0];
			
			if ($result == 0) $this->error("用戶名不存在");
			if ($result == -1) $this->error("密碼錯誤");

			if ($result == 1) {
				$_SESSION[C('USER_AUTH_KEY')] = $username;
			} else {
				$this->error("系統異常，未知錯誤");
			}
		}

		//判斷是否管理組權限
		$inGroups = D('Group')->belongToGroups($username);
		$_SESSION['IN_GROUPS'] = $inGroups;
		if (in_array(1, $inGroups)) {
			$_SESSION[C('ADMIN_AUTH_KEY')] = true;
		}

		$_SESSION['loginName'] = userDisplayName($user);
        //返回跳转模块
        $this->success(C('JUMP_TO_MODULE'));
    }
    
    public function logout()
    {
		session_destroy();
		header("Location:" . __ROOT__ . "/Admin/Auth");
    }
    
    /**
     +----------------------------------------------------------
     * 生成驗證碼  
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
    public function verify() 
    {  
        import("ORG.Util.Image");  
        Image::buildImageVerify();  
    }
}
?>
