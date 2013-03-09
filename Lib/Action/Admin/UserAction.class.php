<?php
// +----------------------------------------------------------------------
// | CommonCMS [IT IS LIFE]
// +----------------------------------------------------------------------
// | Copyright (c) 2013  All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: davidhuang <mchuang1140@gmail.com>
// +----------------------------------------------------------------------
// $Id$

/**
 +------------------------------------------------------------------------------
 * 用戶控制類
 +------------------------------------------------------------------------------
 * @author    davidhuang <mchuang1140@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class UserAction extends EntryAction
{
    
    public function index()
    {
		$userModel = D('User');
		$result = $userModel->getUserList(array('keyword'=>$_GET['keyword']));

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
			$this->success($info);
		} else {
			$this->error('','',0);
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

		$this->assign('groupList', $groupList);
		$this->assign('accessRights', $accessRights);
		$this->assign('members', json_encode($members));
		$this->display();
	}
    
	/**
     +----------------------------------------------------------
     * 取用戶權限，返回權限的詳細，是用戶本身的權限，還是繼承某個群組的權限
     +----------------------------------------------------------
	 * @access public
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

		if (in_array(1, $belongToGroups)) {//系统管理員
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
     */
	private function getModuleAccess($sysID, $userID)
	{
		$groupInfoModel = M('groupInfo');
		$sysAccessAction = M('sysAccessAction');
		$result = array();
		for ($i = 0; $i < 6; $i++) {
			$level = pow(2, $i);
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