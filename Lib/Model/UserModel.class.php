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
 * 用戶模型類
 +------------------------------------------------------------------------------
 * @author    ccxopen <ccxopen@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class UserModel extends Model {
	protected $trueTableName = 'user'; 
    /**
     +----------------------------------------------------------
     * 同步EDB用戶
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function syncFromEDB()
	{
		set_time_limit(0);
		import("@.ORG.NmpsSoapClient");
		$NmpsSoapClient = new NmpsSoapClient();
		$result = $NmpsSoapClient->getBusinessUnitList();
		
		$buID = array();
		foreach ($result as $bu) {
			$buID[] = $bu->businessUnitID;
		}

		$param->buIDs = $buID;
		$result = $NmpsSoapClient->getUserListByBUID($param);

		$this->syncUsers($result);

		$this->syncGroupMembers();

		$this->setupStatus();
	}

	 /**
     +----------------------------------------------------------
     * 同步EDB的群組成員
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function syncGroupMembers()
	{
		import("@.ORG.NmpsSoapClient");
		$NmpsSoapClient = new NmpsSoapClient();
		$businessUnits = $NmpsSoapClient->getBusinessUnitList();

		$model = M('groupMember');

		//搜索系統中已經用到的ＥＤＢ群組
		$EDBGroup = $model->query("select groupID,unit,unitID,unitName from group_member where source='group' group by unitName,groupID");

		foreach ($businessUnits as $bu) {
			$buID = $bu->businessUnitID;

			$exitsGroups = $this->getExistsUnit('bu', $buID, $EDBGroup);
			foreach ($exitsGroups as $group) {
				$data['groupID'] = $group['groupID'];
				$data['source'] = 'group';
				$data['unit'] = 'bu';
				$data['unitID'] = $buID;
				$data['unitName'] = $bu->businessUnit . '(' . $bu->businessUnitCN . ')';

				$param->buIDs = array($buID);
		        $users = $NmpsSoapClient->getUserListByBUID($param);
				if (!is_soap_fault($users)) {
					$model->where("groupID='${data['groupID']}' and source = 'group' and unit = 'bu' and unitID = '$buID'")->delete();
					
					//如果群組中沒有用戶，加入一個空白用戶的組，否則無法關聯沒有用戶的EDB組
					if (!$users) {
						$data['userID'] = '';
						$model->add($data);
						continue;
					}
					
					foreach ($users as $user) {
						$data['userID'] = trim($user->loginID);
						if (!$data['userID']) continue;
						$model->add($data);
					}
				}
			}

			$this->syncDepartmentMembersOfBU($bu, $EDBGroup);
		}
	}

	/**
     +----------------------------------------------------------
     * 同步EDB的部門成員
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @param $bu 
	 * @param $EDBGroup 
	 +----------------------------------------------------------
     */
	private function syncDepartmentMembersOfBU($bu, $EDBGroup)
	{
		import("@.ORG.NmpsSoapClient");
		$NmpsSoapClient = new NmpsSoapClient();
		$param->businessUnitID = $bu->businessUnitID;
		$departments = $NmpsSoapClient->getDepartmentList($param);

		$model = M('groupMember');

		foreach ($departments as $dept) {
			$departmentID = $dept->departmentID;

			$exitsGroups = $this->getExistsUnit('dept', $departmentID, $EDBGroup);
			foreach ($exitsGroups as $group) {
				$data['groupID'] = $group['groupID'];
				$data['source'] = 'group';
				$data['unit'] = 'dept';
				$data['unitID'] = $departmentID;
				$data['unitName'] = $bu->businessUnit . '(' . $bu->businessUnitCN . ')－>' . $dept->department;

				$param->deptIDs = array($departmentID);
		        $users = $NmpsSoapClient->getUserListByDepartmentID($param);
				if (!is_soap_fault($users)) {
					$model->where("groupID='${data['groupID']}' and source = 'group' and unit = 'dept' and unitID = '$departmentID'")->delete();
					
					//如果群組中沒有用戶，加入一個空白用戶的組，否則無法關聯沒有用戶的EDB組
					if (!$users) {
						$data['userID'] = '';
						$model->add($data);
						continue;
					}
					foreach ($users as $user) {
						$data['userID'] = trim($user->loginID);
						if (!$data['userID']) continue;
						$model->add($data);
					}
				}
			}

			$this->syncTeamMembersOfDepartment($bu, $dept, $EDBGroup);
		}
	}

	/**
     +----------------------------------------------------------
     * 同步EDB的team成員
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @param $bu 单位
	 * @param $dept 部门
	 * @param $EDBGroup EDB群组
	 * +----------------------------------------------------------
	 * 
     */
	private function syncTeamMembersOfDepartment($bu, $dept, $EDBGroup)
	{
		import("@.ORG.NmpsSoapClient");
		$NmpsSoapClient = new NmpsSoapClient();
		$param->departmentID = $dept->departmentID;
		$teams = $NmpsSoapClient->getTeamList($param);

		$model = M('groupMember');

		foreach ($teams as $team) {
			$teamID = $team->teamID;

			$exitsGroups = $this->getExistsUnit('team', $teamID, $EDBGroup);
			foreach ($exitsGroups as $group) {
				$data['groupID'] = $group['groupID'];
				$data['source'] = 'group';
				$data['unit'] = 'team';
				$data['unitID'] = $teamID;
				$data['unitName'] = $bu->businessUnit . '(' . $bu->businessUnitCN . ')－>' . $dept->department . '－>' . $team->team;

				$param->teamIDs = array($teamID);
		        $users = $NmpsSoapClient->getUserListByTeamID($param);
				if (!is_soap_fault($users)) {
					$model->where("groupID='${data['groupID']}' and source = 'group' and unit = 'team' and unitID = '$teamID'")->delete();
					
					//如果群組中沒有用戶，加入一個空白用戶的組，否則無法關聯沒有用戶的EDB組
					if (!$users) {
						$data['userID'] = '';
						$model->add($data);
						continue;
					}
					foreach ($users as $user) {
						$data['userID'] = trim($user->loginID);
						if (!$data['userID']) continue;
						$model->add($data);
					}
				}
			}
		}
	}



	/**
     +----------------------------------------------------------
     * 同步EDB的群組成員
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @param $unitType：unit类型
	 * @param $unitID：unit ID
	 * @param $units: units数组
	 +----------------------------------------------------------
	 * @return array units
	 +----------------------------------------------------------
	 * 
     */
	private function getExistsUnit($unitType, $unitID, $units)
	{
		$result = array();
		foreach ($units as $unit) {
			if ($unit['unit'] == $unitType && $unit['unitID'] == $unitID) {
				$result[] = $unit;
			}
		}
		return $result;
	}


	/**
     +----------------------------------------------------------
     * 同步多個用戶
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @param $users
	 +----------------------------------------------------------
     */
	public function syncUsers($users)
	{
		foreach ($users as $user) {
			unset($newUser);
			$newUser['userID'] = trim($user->loginID);
			$newUser['email'] = $user->email;
			$middleName = ucfirst($user->middleName);
			if ($middleName) {
				$newUser['firstname'] = $middleName . ' ' . ucfirst($user->firstName);
			} else {
				$newUser['firstname'] = ucfirst($user->firstName);
			}
			$chineseName = $user->chineseName;
			if ($chineseName) {
				$newUser['firstname'] .= " | $chineseName";
			}
			
			$newUser['lastname'] = ucfirst($user->lastName);
			$newUser['nickname'] = $user->nickName;
			$newUser['direct'] = $user->direct;
			$newUser['extension'] = $user->extension;
			$newUser['mobile'] = $user->mobile;
			$newUser['EDBStatus'] = $user->status;
			$newUser['teamName'] = $user->teamName;
			$newUser['source'] = 'edb';
			
			if (!$newUser['userID']) continue;

			//是否已經存在於本地數據庫
			$count = $this->where("userID = '${newUser['userID']}'")->count();
			if ($count) {
				//如果已存在，更新用戶資料
				$this->where("userID = '${newUser['userID']}'")->save($newUser);
			} else {
				//新增員工，默認為失效
				$newUser['status'] = 0;
				$this->add($newUser);
			}
		}
	}

	/**
     +----------------------------------------------------------
     * 設置用戶狀態
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function setupStatus()
	{
		$groupModel = D('Group');
		$memberModel = M('groupMember');

		$users = $this->select();

		foreach ($users as $user) {
			$status = $groupModel->inGroup($user['userID'], C('SYS_USER_GROUP_ID'));
			$status = intval($status);
			$this->where("userID='${user['userID']}'")->save(array('status'=>$status));
			if (!$status) {
				$memberModel->where("source = 'single' and userID = '$userID'")->delete();
			}
		}
	}

	/**
     +----------------------------------------------------------
     * 取用戶列表
     +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param  $cond 查询条件
     +----------------------------------------------------------
	 * @return array page、查询用户列表、记录数
	 +----------------------------------------------------------
     */
	public function getUserList($cond)
	{
		import("@.ORG.Page"); //导入分页类
		
		//创建查询条件SQL
		$where = $this->buildCondition($cond, 1);	
		
		//分页器
		$count = $this->where($where)->count(); //计算总数
		$p = new Page ($count, 10);
		$page = $p->show();

		$list = $this->where($where)->limit($p->firstRow.','.$p->listRows)->order("lastname")->select();
		$result['page'] = $page;
		$result['data'] = $list;
		$result['total'] = $count; //搜索的记录数，搜索日志用到
		return $result;
	}

	/**
     +----------------------------------------------------------
     * 创建查询条件SQL
     +----------------------------------------------------------
	 * @access private
	 +----------------------------------------------------------
	 * @param $cond 查询条件
	 * @param $status 用户状态
     +----------------------------------------------------------
	 * @return 查询条件SQL
	 +----------------------------------------------------------
     */
	private function buildCondition($cond, $status) {
		$where = "status=$status ";
		if ($cond['keyword']) {
			$where .= " and (userID like('%${cond['keyword']}%') or email like('%${cond['keyword']}%') or firstname like('%${cond['keyword']}%') or lastname like('%${cond['keyword']}%') or nickname like('%${cond['keyword']}%')) ";
		}
		return $where;
	}
}

?>