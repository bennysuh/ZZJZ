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
 * 用戶控制類
 +------------------------------------------------------------------------------
 * @author    ccxopen <ccxopen@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class UserAction extends EntryAction
{
    /**
     +----------------------------------------------------------
     * 根据查询条件显示用户列表
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
    public function index()
    {
		$userModel = D('User');
		//根据URL参数查询用户
		$result = $userModel->getUserList(array('keyword'=>$_GET['keyword']));//URL有多个keyword$_GET只获取最后一个keyword的值

		$this->assign('page', $result['page']);
		$this->assign('list', $result['data']);
		$this->assign('keyword', $_GET['keyword']);
		
		$this->display();
	}

	/**
     +----------------------------------------------------------
     * 新建本地用戶
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function addUser()
	{
		$userID = $_POST['userID'];
		//如果同步EDB用戶，先要檢查EDB中是否已經存在此用戶名
		if (C('ENABLE_EDB')) {
			import("@.ORG.NmpsSoapClient");
			$NmpsSoapClient = new NmpsSoapClient();
			$params->loginID = $userID;
			$result = $NmpsSoapClient->isUserExists($params);//isUserExists 调用Java的方法
			$result = $result[0];
			if ($result) {
				$this->error("用户名已存在");
			}
		}

		$userModel = M('user');
		//是否已經存在於本地數據庫
		$count = $userModel->where("userID = '$userID'")->count();
		if ($count) {
			$this->error("用户名已存在");
		}
		
        if ($userModel->add($_POST)) {
			D('Group')->addUserToGroup(C('SYS_USER_GROUP_ID'), trim($userID));
			$this->success('增加用戶成功');
		} else {
			$this->error('增加用戶失敗');
		}
	}

	/**
     +----------------------------------------------------------
     * 刪除本地用戶
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function removeUser()
	{
		$userID = $_POST['userID'];
		M('user')->where("userID='$userID'")->save(array('status'=>0));
		M('groupMember')->where("source = 'single' and userID = '$userID'")->delete();
		$this->success('刪除用戶成功');
	}

	/**
     +----------------------------------------------------------
     * 取用戶信息
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function getUserInfo()
	{
		$userID = $_POST['userID'];
		$info = M('user')->where("userID='$userID'")->find();
		if ($info) {
			$this->ajaxReturn($info);
		} else {
			$this->ajaxReturn('','',0);
		}
	}

	/**
     +----------------------------------------------------------
     * 保存用戶信息
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function saveUserInfo()
	{
		$userID = $_POST['userID'];
		
		$data['email'] = $_POST['email'];
		$data['firstname'] = $_POST['firstname'];
		$data['lastname'] = $_POST['lastname'];
		$data['nickname'] = $_POST['nickname'];
		if (trim($_POST['password'])) {
			$data['password'] = trim($_POST['password']);
		}

		M('user')->where("userID='$userID'")->save($data);
		$this->success('编辑用戶成功');
	}

	/**
     +----------------------------------------------------------
     * 同步EDB用戶
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function syncFromEDB()
	{
		$this->display();
	}

	/**
     +----------------------------------------------------------
     * 同步EDB用戶
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function doSyncFromEDB()
	{
		if (!C('ENABLE_EDB')) {
			$this->error('當前的系統設置不允許同步EDB員工');
		}

		$userModel = D('User');
		
		$succ = true;
		try {
			$userModel->syncFromEDB();
		} catch(Exception $fault) {
			$succ = false;
			SysLogs::log("同步EDB失敗");
			$this->error('同步失敗');
		}
		if ($succ) {
			SysLogs::log("同步EDB成功");
			$this->success('同步成功');
		}
	}

	/**
     +----------------------------------------------------------
     * 用戶權限
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function privileges()
	{
		$userID = $_GET['userID'];
		
		$groupModel = D('Group');

		$groups = $groupModel->belongToGroups($userID);

		$groups = implode(',', $groups);

		$groupList = M('groupInfo')->where("groupID in ($groups)")->select();

		$accessRights = M('sysAccess')->select();

		$members = array();
		foreach($accessRights as $key => $row) {
			$access = $this->getModuleAccess($row['sysID'], $userID);
			$accessRights[$key]['access'] = $access;
			for ($i = 0; $i < 6; $i++) {
				$level = pow(2, $i);
				$members[$key][$level] = $access[$level]['member'];
			}			
		}

		//var_dump($accessRights);
		//页面赋值
		$this->assign('groupList', $groupList);//所属群组
		$this->assign('accessRights', $accessRights);//权限
		$this->assign('members', json_encode($members));//成员权限
		$this->display();
	}
    
	/**
     +----------------------------------------------------------
     * 取用戶權限，返回權限的詳細，是用戶本身的權限，還是繼承某個群組的權限
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @param $sysID 系统功能ID
	 * @param $userID 用户ID
	 * @param $level 用户权限数值 2 4 8 16 32 
	 +----------------------------------------------------------
	 * @return array 用户权限集合
	 +----------------------------------------------------------
     */
	private function getUserAccess($sysID, $userID, $level)
	{
		//取得用户所属的群组
		$groupModel = D('Group');
		$belongToGroups = $groupModel->belongToGroups($userID);
		if (!$belongToGroups) {
			$groups = '-1';
		} else {
			$groups = implode(',', $belongToGroups);
		}

		if (in_array(1, $belongToGroups)) {//系統管理員
			$tmp['groupID'] = 1;
			$list[] = $tmp;
		} else {
			$list = M('sys_access_level')->where("sysID = $sysID and (userID = '$userID' or groupID in ( $groups )) and ($level & level) > 0")->select();
		}

		return $list ? $list : array();
	}

	/**
     +----------------------------------------------------------
     * 取用戶某個模塊的全部權限信息
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @param $sysID 系统功能ID
	 * @param $userID 用户ID
	 +----------------------------------------------------------
	 * @return array 模块权限信息
	 +----------------------------------------------------------
     */
	private function getModuleAccess($sysID, $userID)
	{
		$groupInfoModel = M('groupInfo');
		$sysAccessAction = M('sysAccessAction');
		$result = array();
		for ($i = 0; $i < 6; $i++) {
			$level = pow(2, $i);//返回2的$i次方
			$access = $this->getUserAccess($sysID, $userID, $level);
			
			$accessInfo = array();
			foreach ($access as $item) {
				$tmp['groupName'] = '';
				$tmp['userID'] = '';
				if ($item['groupID'] > 0) {
					$groupInfo = $groupInfoModel->getByGroupid($item['groupID']);
					$tmp['groupName'] = $groupInfo['groupName'];
				} else {
					$tmp['userID'] = $item['userID'];
				}
				$accessInfo[] = $tmp;
			}

			$result[$level]['member'] = $accessInfo;

			$count = $sysAccessAction->where("sysID=$sysID and level=$level")->count();
			if ($count) {
				$result[$level]['enable'] = true;
			} else {
				$result[$level]['enable'] = false;
			}
		}

		return $result;
	}
}
?>