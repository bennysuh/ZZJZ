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
 * 用戶模型類
 +------------------------------------------------------------------------------
 * @author    davidhuang <mchuang1140@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class UserModel extends Model {
	/**
     +----------------------------------------------------------
     * 
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
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
     * 設置用戶狀態
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function setupStatus()
	{
		$groupModel = D('Group');

		$users = $this->select();

		foreach ($users as $user) {
			$status = $groupModel->inGroup($user['userID'], C('SYS_USER_GROUP_ID'));
		    $status = intval($status);

			$this->where("userID='${user['userID']}'")->save(array('status'=>$status));
			if (!$status) {
				$groupModel->removeUserFromGroup($user['userID']);
			}
		}
	}

	/**
     +----------------------------------------------------------
     * 取用戶列表
     +----------------------------------------------------------
	 * @access public
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

	//创建查询条件SQL
	private function buildCondition($cond, $status) {
		$where = "status=$status ";
		if ($cond['keyword']) {
			$where .= " and (userID like('%${cond['keyword']}%') or email like('%${cond['keyword']}%') or firstname like('%${cond['keyword']}%') or lastname like('%${cond['keyword']}%') or nickname like('%${cond['keyword']}%')) ";
		}
		return $where;
	}
}

?>