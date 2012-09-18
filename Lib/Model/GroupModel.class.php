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
 * 群組模型類
 +------------------------------------------------------------------------------
 * @author    ccxopen <ccxopen@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class GroupModel extends Model {
	protected $trueTableName = 'group_member'; 
    /**
     +----------------------------------------------------------
     * 判斷某用戶是否屬於某個系統群組
     +----------------------------------------------------------
	 * @access public
	 * @param userID
	 * @param groupID
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
	 * @param $userID 用户ID
	 +----------------------------------------------------------
	 * @return array 用户所在群组
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
	 * @param groupID
	 +----------------------------------------------------------
	 * @return array 父群组
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
	 * @param path groupID的array
	 * @param currentGroup  
	 +----------------------------------------------------------
	 * @return array 父群组
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
	 * @param $groupID 群组ID
	 +----------------------------------------------------------
	 * @return array 群组成员
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
	 * @param 群组ID
	 * @param userID
	 +----------------------------------------------------------
	 * @return boolean 是否添加成功
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
}

?>