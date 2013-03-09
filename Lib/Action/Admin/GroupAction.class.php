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
 * 群組控制類
 +------------------------------------------------------------------------------
 * @author    davidhuang <mchuang1140@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class GroupAction extends EntryAction
{
    
    public function index()
    {
		$list = M('groupInfo')->where("status=1")->order('fixed desc,groupName')->select();
		
		$this->assign('list', $list);
		$this->display();
	}

	/**
     +----------------------------------------------------------
     * 保存或增加群組基本信息
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function saveGroups()
	{
		$json = $_POST['json'];
		$data = objectToArray(json_decode($json));

		$log = '';
		foreach ($data as $row) {
			$row['groupName'] = trim($row['groupName']);
			if (!$row['groupName']) continue;//群組名不能為空字串
			if ($row['id'] == 0) {//新增群组
			    //檢查是否已經存在同名群組
				$count = M('groupInfo')->where("groupName = '${row['groupName']}' and status = 1")->count();
				if ($count) continue;
				M('groupInfo')->add($row);
				$log .= "新增了群組${row['groupName']}；";
			} else {
				M('groupInfo')->where("groupID = ${row['id']}")->save($row);
				$log .= "修改了群組${row['groupName']}；";
			}
		}

		SysLogs::log($log);
		
		$this->success('操作成功');
	}

	/**
     +----------------------------------------------------------
     * 删除群组
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function removeGroups()
	{
		$ids = $_POST['ids'];
		M('groupInfo')->where("groupID in ($ids)")->save(array('status'=>0));
        
		//刪除群組成員
		M('groupMember')->where("groupID in ($ids)")->delete();

		//此群組如果是其他群組下面的成員，從其他群組中脫離關係
		M('groupMember')->where("source='localGroup' and unitID in ($ids)")->delete();

		//刪除權限表中與該群組有關的記錄
        M('sysAccessLevel')->where("groupID in ($ids)")->delete();

		$list = M('groupInfo')->where("groupID in ($ids)")->select();
		$groupsName = '';
		foreach ($list as $row) {
			$groupsName .= $row['groupName'] . '；';
		}

		SysLogs::log("刪除了群組：$groupsName");

		$this->success('操作成功');
	}


	/**
     +----------------------------------------------------------
     * 取群組的成員
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function getGroupMember()
	{
		$groupID = $_POST['groupID'];

		$model = M('groupMember');
		
		$localGroup = $model->where("groupID=$groupID and source='localGroup'")->order('unitName')->select();
		$field = "firstname,lastname,nickname,group_member.*";
		$users = $model->join("user on user.userID=group_member.userID")->field($field)->order('lastname')->where("groupID=$groupID and group_member.source='single'")->select();
		
		foreach ($users as $key => $user) {
			$users[$key]['displayName'] = userDisplayName($user);
			unset($users[$key]['firstname']);
			unset($users[$key]['lastname']);
			unset($users[$key]['nickname']);
		}

		$localGroup = $localGroup ? $localGroup : array();
		$users = $users ? $users : array();
		$list = array_merge($localGroup, $users);

		$this->success($list);
	}

	/**
     +----------------------------------------------------------
     * 取本地群组列表，用於生成下拉菜單
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function getGroupList()
	{
		$list = D("Group")->getGroups();
		$this->success($list);
	}

	/**
     +----------------------------------------------------------
     * 取用戶信息，用於下拉列表自動完成
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function getUsersForAutoSelection()
	{
		$q = $_POST['q'];
		$ignoreStatus = $_GET['ignoreStatus'];
		$list = D("Group")->getUserList($q, $ignoreStatus);	
		$result = array();
		foreach ($list as $user) {
			$tmp['userID'] = $user['userID'];
			$tmp['name'] = userDisplayName($user);
			$result[] = $tmp;
		}
		echo json_encode($result);
	}
	
	/**
     +----------------------------------------------------------
     * 取BU列表
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function getBUList()
	{
		$list = D("Group")->getBUs();
		$this->ajaxReturn($list);
	}
	
	
	/**
     +----------------------------------------------------------
     * 取部門列表
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function getDepartmentList()
	{
		$list = D("Group")->getDeptList($_POST['BUID']);
		$this->ajaxReturn($list);
	}
	
	/**
     +----------------------------------------------------------
     * 取team列表
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function getTeamList()
	{
		$list = D("Group")->getTeams($_POST['departmentID']);
		$this->ajaxReturn($list);
	}
	
	/**
     +----------------------------------------------------------
     * 增加群組成員
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function addMember()
	{
		$groupID = trim($_POST['groupID']);

		$json = $_POST['json'];
		$data = objectToArray(json_decode($json));

		$groupInfo = M('groupInfo')->getByGroupid($groupID);

		$log = "對群組${groupInfo['groupName']}增加了以下成員：";
		
		foreach ($data as $row) {
			if ($row['type'] == 'user') {
				D('Group')->addUserToGroup($groupID, trim($row['userID']));
				$log .= "用戶：${row['userID']}；";
			} else {
				$this->addLocalGroup($groupID, $row);
				$log .= "本地群組：${row['groupName']}；";
			}
		}

		D('User')->setupStatus();

		SysLogs::log($log);

		$this->success('操作成功');

	}

	/**
     +----------------------------------------------------------
     * 增加用戶到指定群組
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	private function addLocalGroup($groupID, $info)
	{
		if (!$groupID) return;
		$data['groupID'] = $groupID;
		$data['source'] = 'localGroup';
		$data['unitID'] = $info['groupID'];

		//檢查是否已經曾經加入了
		$count = M('groupMember')->where($data)->count();
		if ($count) return;

		$data['unitName'] = $info['groupName'];

		M('groupMember')->add($data);
	}

	
	/**
     +----------------------------------------------------------
     * 刪除群組成員
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function removeMember()
	{
		$json = $_POST['json'];
		$data = objectToArray(json_decode($json));

		$groupID = $_POST['groupID'];

		$model = M('groupMember');

		$groupInfo = M('groupInfo')->getByGroupid($groupID);

		$log = "對群組${groupInfo['groupName']}刪除了以下成員：";

		foreach ($data as $row) {
			if ($row['source'] == 'single') {
				$model->where("groupID=$groupID and source = 'single' and userID = '${row['userID']}'")->delete();
				$log .= "用戶：${row['userID']}；";
			} else {
				$info = $model->where("groupID=$groupID and source = 'localGroup' and unitID = '${row['unitID']}'")->find();
				$log .= "本地群組：${info['unitName']}；";

				$model->where("groupID=$groupID and source = 'localGroup' and unitID = '${row['unitID']}'")->delete();
			}
		}

		D('User')->setupStatus();

		SysLogs::log($log);

		$this->success('操作成功');
	}
	/**
     +----------------------------------------------------------
     * 获取本地群組信息 
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function getGroupInfo()
	{
		$groupId = $_POST["groupId"];
		if($groupId){
			$result = M("groupInfo")->where("groupID=".$groupId)->find();
			echo json_encode($result);
		}else{
			echo false;
		}
	}
}
?>