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
 * 群組模型類
 +------------------------------------------------------------------------------
 * @author    davidhuang <mchuang1140@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class GroupModel extends Model {

    /**
     +----------------------------------------------------------
     * 判斷某用戶是否屬於某個系统群組
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function inGroup($userID, $groupID)
	{
		$groups = $this->belongToGroups($userID);
		if (in_array($groupID, $groups)) {
			return true;
		} else {
			return false;
		}
	}

	/**
     +----------------------------------------------------------
     * 獲取某個用戶所屬的直接或間接的群組
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function belongToGroups($userID)
	{
		$list = M('groupMember')->query("select distinct(groupID) from group_member where userID = '$userID'");

		$groups = array();
		foreach($list as $row) {
			$groups[] = $row['groupID'];
			$parentGroups = $this->parentGroups($row['groupID']);
			$parentGroups = $parentGroups ? $parentGroups : array();
			$groups = array_merge($groups, $parentGroups);
		}
		return array_unique($groups);
	}

	/**
     +----------------------------------------------------------
     * 獲取某個群组的直接或间接的父群组
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	private function parentGroups($groupID)
	{
		return $this->travelToTop(array($groupID), $groupID);
	}

	/**
     +----------------------------------------------------------
     * 从底向上查找某群组的父群组
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	private function travelToTop($path, $currentGroup)
	{
		$list = M('groupMember')->query("select groupID from group_member where source = 'localGroup' and unitID = $currentGroup");

		$groups = array();
		foreach($list as $row) {
			//如果出現環路，忽略此條路徑
			if (in_array($row['groupID'], $path)) continue;

			$groups[] = $row['groupID'];

			//繼續往上查
			$path[] = $row['groupID'];//加入已走过的路径
			$parentGroups = $this->travelToTop($path, $row['groupID']);
			$parentGroups = $parentGroups ? $parentGroups : array();
			$groups = array_merge($groups, $parentGroups);
		}
		return $groups;
	}

	/**
     +----------------------------------------------------------
     * 獲取某個群組的所有成員
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function getGroupUsers($groupID)
	{
		$list = M('groupMember')->where("groupID = $groupID")->select();

		$users = array();
		foreach($list as $row) {
			if ($row['source'] == 'single' || $row['source'] == 'group') {
				if ($row['userID']) {
					$users[] = $row['userID'];
				}
			} else if ($row['source'] == 'localGroup') {
				$users = array_merge($users, $this->getGroupUsers($row['unitID']));
			}
		}

		return $users;
	}

	/**
     +----------------------------------------------------------
     * 增加用戶到指定群組
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function addUserToGroup($groupID, $userID)
	{
		if (empty($groupID) || empty($userID)) return false;

		//驗證用戶是否存在
		$count = M('user')->where("userID = '$userID'")->count();
		if (!$count) return false;

		$data['groupID'] = $groupID;
		$data['userID'] = $userID;
		$data['source'] = 'single';

		//檢查是否已經曾經加入了
		$count = M('groupMember')->where($data)->count();
		if ($count) return true;

		M('groupMember')->add($data);

		//設置狀態為激活
		M('user')->where("userID = '$userID'")->save(array('status'=>1));

		return true;
	}

	/**
     +----------------------------------------------------------
     * 从群组中删除指定用户
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function removeUserFromGroup($userID, $groupID = 0)
	{
		if (empty($userID)) return false;

		if ($groupID) {
			M('groupMember')->where("groupID = $groupID and userID = '$userID' and source = 'single'")->delete();
		} else {
			M('groupMember')->where("userID = '$userID' and source = 'single'")->delete();
		}
		return true;
	}


	/**
     +----------------------------------------------------------
     * 取本地群组列表，用於生成下拉菜單
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function getGroups()
	{
		$result = M('groupInfo')->where("status=1")->order('fixed desc,groupID')->select();

		$list = array();
		foreach ($result as $groupInfo) {
			$tmp['id'] = $groupInfo['groupID'];
			$tmp['name'] = $groupInfo['groupName'];
			$list[] = $tmp;
		}
		return $list;
	}


	/**
     +----------------------------------------------------------
     * 取用戶信息，用於下拉列表自動完成
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function getUserList($q, $ignoreStatus)
	{
		if ($ignoreStatus) {
			$where = "1=1 ";
		} else {
			$where = "status=1 ";
		}
		
		if ($q) {
			$where .= " and (userID like ('%${q}%') or firstname like ('%${q}%') or lastname like ('%${q}%') or nickname like ('%${q}%'))";
		}
		
		$list = M('user')->where($where)->order('lastname')->select();
		return $list;
	}


	
}

?>