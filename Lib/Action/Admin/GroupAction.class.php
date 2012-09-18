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
 * 群組控制類
 +------------------------------------------------------------------------------
 * @author    ccxopen <ccxopen@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class GroupAction extends EntryAction
{
    /**
     +----------------------------------------------------------
     * 显示群组列表
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
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
		
		$EDBGroup = $model->query("select source,unit,unitID,unitName from group_member where groupID=$groupID and source='group' group by unitName order by unitName");
		$localGroup = $model->where("groupID=$groupID and source='localGroup'")->order('unitName')->select();
		$field = "firstname,lastname,nickname,group_member.*";
		$users = $model->join("user on user.userID=group_member.userID")->field($field)->order('lastname')->where("groupID=$groupID and group_member.source='single'")->select();
		
		foreach ($users as $key => $user) {
			$users[$key]['displayName'] = userDisplayName($user);
			unset($users[$key]['firstname']);
			unset($users[$key]['lastname']);
			unset($users[$key]['nickname']);
		}

		$EDBGroup = $EDBGroup ? $EDBGroup : array();
		$localGroup = $localGroup ? $localGroup : array();
		$users = $users ? $users : array();
		$list = array_merge($EDBGroup, $localGroup, $users);

		$this->ajaxReturn($list);
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
		$result = M('groupInfo')->where("status=1")->order('fixed desc,groupID')->select();

		$list = array();
		foreach ($result as $groupInfo) {
			$tmp['id'] = $groupInfo['groupID'];
			$tmp['name'] = $groupInfo['groupName'];
			$list[] = $tmp;
		}

		$this->ajaxReturn($list);
	}

	/**
     +----------------------------------------------------------
     * 取用戶信息，用於下拉列表自動完成
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @return json 群组成员
	 +----------------------------------------------------------
     */
	public function getUsersForAutoSelection()
	{
		$q = $_POST['q'];
		$ignoreStatus = $_GET['ignoreStatus'];

		if ($ignoreStatus) {
			$where = "1=1 ";
		} else {
			$where = "status=1 ";
		}
		
		if ($q) {
			$where .= " and (userID like ('%${q}%') or firstname like ('%${q}%') or lastname like ('%${q}%') or nickname like ('%${q}%'))";
		}
		
		$list = M('user')->where($where)->order('lastname')->select();
		
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
	 * @return ajax BU列表
	 +----------------------------------------------------------
     */
	public function getBUList()
	{
		import("@.ORG.NmpsSoapClient");
		$NmpsSoapClient = new NmpsSoapClient();
		$result = $NmpsSoapClient->getBusinessUnitList();
		
		$list = array();
		foreach ($result as $bu) {
			$tmp['id'] = $bu->businessUnitID;
			$tmp['name'] = $bu->businessUnit . '(' . $bu->businessUnitCN . ')';
			$list[] = $tmp;
		}

		$this->ajaxReturn($list);
	}

	/**
     +----------------------------------------------------------
     * 取部門列表
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @param POST BUID
	 +----------------------------------------------------------
	 * @return ajax 部门列表
	 +----------------------------------------------------------
     */
	public function getDepartmentList()
	{
		import("@.ORG.NmpsSoapClient");
		$NmpsSoapClient = new NmpsSoapClient();
		$param->businessUnitID = $_POST['BUID'];
		$result = $NmpsSoapClient->getDepartmentList($param);
		
		$list = array();
		foreach ($result as $dept) {
			$tmp['id'] = $dept->departmentID;
			$tmp['name'] = $dept->department;
			$list[] = $tmp;
		}

		$this->ajaxReturn($list);
	}

	/**
     +----------------------------------------------------------
     * 取team列表
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @param POST departmentID
	 +----------------------------------------------------------
	 * @return ajax team列表
	 +----------------------------------------------------------
     */
	public function getTeamList()
	{
		import("@.ORG.NmpsSoapClient");
		$NmpsSoapClient = new NmpsSoapClient();
		$param->departmentID = $_POST['departmentID'];
		$result = $NmpsSoapClient->getTeamList($param);
		
		$list = array();
		foreach ($result as $bu) {
			$tmp['id'] = $bu->teamID;
			$tmp['name'] = $bu->team;
			$list[] = $tmp;
		}

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
			} else if ($row['type'] == 'group') {
				$this->addEDBGroupToGroup($groupID, $row);

				$unitName = $row['BUName'];
				if ($row['department']) $unitName .= '－>' . $row['department'];
				if ($row['team']) $unitName .= '－>' . $row['team'];
				$log .= "EDB群組：${unitName}；";
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
	 * @param $groupID
	 * @param $info
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
     * 增加EDB群組到指定的系統群組
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @param $groupID
	 * @param $EDB
	 +----------------------------------------------------------
     */
	private function addEDBGroupToGroup($groupID, $EDB)
	{
		$BUID = $EDB['BUID'];
		$departmentID = $EDB['departmentID'];
		$teamID = $EDB['teamID'];

        import("@.ORG.NmpsSoapClient");
		$NmpsSoapClient = new NmpsSoapClient();
		
		$userModel = D('User');
		if ($teamID) {
			$param->teamIDs = array($teamID);
		    $users = $NmpsSoapClient->getUserListByTeamID($param);
			$userModel->syncUsers($users);
			$this->insertEDBRecord($groupID, $users, 'team', $EDB);
		} else if ($departmentID) {
			$param->deptIDs = array($departmentID);
		    $users = $NmpsSoapClient->getUserListByDepartmentID($param);
			$userModel->syncUsers($users);
			$this->insertEDBRecord($groupID, $users, 'dept', $EDB);
		} else {
			$param->buIDs = array($BUID);
		    $users = $NmpsSoapClient->getUserListByBUID($param);
			$userModel->syncUsers($users);
			$this->insertEDBRecord($groupID, $users, 'bu', $EDB);
		}
	}

	/**
     +----------------------------------------------------------
     * 增加ＥＤＢ的用戶到指定群組
     +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param $groupID
	 * @param $users
	 * @param $EDBGroupType
	 * @param $EDBgroupInfo
     +----------------------------------------------------------
	 * @return void
	 +----------------------------------------------------------
     */
	private function insertEDBRecord($groupID, $users, $EDBGroupType, $EDBgroupInfo)
	{
		if (!$groupID) return;

		$data['groupID'] = $groupID;
		$data['source'] = 'group';
		$data['unit'] = $EDBGroupType;
		
		if ($EDBGroupType == 'team') {
			$data['unitID'] = $EDBgroupInfo['teamID'];
			$data['unitName'] = $EDBgroupInfo['BUName'] . '－>' . $EDBgroupInfo['department']. '－>' . $EDBgroupInfo['team'];
		} else if ($EDBGroupType == 'dept') {
			$data['unitID'] = $EDBgroupInfo['departmentID'];
			$data['unitName'] = $EDBgroupInfo['BUName'] . '－>' . $EDBgroupInfo['department'];
		} else {
			$data['unitID'] = $EDBgroupInfo['BUID'];
			$data['unitName'] = $EDBgroupInfo['BUName'];
		}

		$model = M('groupMember');
		
		//如果群組中沒有用戶，加入一個空白用戶的組，否則無法關聯沒有用戶的EDB組
		if (!$users) {
			$data['userID'] = '';

			//檢查是否之前已經加入過
			$tmp = $data;
			unset($tmp['unitName']);//名稱可能在ＥＤＢ發生過變化，所以不要對比名稱
			$count = $model->where($tmp)->count();
			if ($count) return;

			$model->add($data);
			return;
		}

		foreach ($users as $user) {
			$data['userID'] = trim($user->loginID);
			if (!$data['userID']) continue;

			//檢查是否之前已經加入過
			$tmp = $data;
			unset($tmp['unitName']);//名稱可能在ＥＤＢ發生過變化，所以不要對比名稱
			$count = $model->where($tmp)->count();
			if ($count) continue;
			$model->add($data);			
		}
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
			} else if ($row['source'] == 'group') {
				$info = $model->where("groupID=$groupID and source = 'group' and unit = '${row['unit']}' and unitID = '${row['unitID']}'")->find();
				$log .= "EDB群組：${info['unitName']}；";

				$model->where("groupID=$groupID and source = 'group' and unit = '${row['unit']}' and unitID = '${row['unitID']}'")->delete();
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

	


	

	
    
}
?>