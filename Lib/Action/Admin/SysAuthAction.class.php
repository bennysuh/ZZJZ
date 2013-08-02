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
 * 權限管理類
 +------------------------------------------------------------------------------
 * @author    davidhuang <mchuang1140@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class SysAuthAction extends EntryAction
{
    
    public function index()
    {
		$list = M('sysAccess')->order('sysID')->select();
		
		$this->assign('list', $list);
		$this->display();
	}

	/**
     +----------------------------------------------------------
     * 系统權限設置
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function updateSysAccess()
	{
		$sysID = trim($_POST['sysID']);

		$json = $_POST['json'];
		$list = objectToArray(json_decode($json));

		$sysInfo = M('sysAccess')->getBySysid($sysID);

		$log = "對系统功能模塊[${sysInfo['name']}]作了以下修改：";

		$data['sysID'] = $sysID;

		foreach ($list as $row) {
			$data['level'] = $row['level'];		
			if ($row['id'] == 0) { //新增
			    if (!$row['groupID'] && !$row['userID']) continue;
			    $data['groupID'] = $row['groupID'] ? $row['groupID'] : '';
			    $data['userID'] = $row['userID'] ? $row['userID'] : '';
               
				//檢查是否之前已經加入過
				$tmp = $data;
				unset($tmp['level']);
				$count = M('sysAccessLevel')->where($tmp)->count();
				if ($count) continue;

			    M('sysAccessLevel')->add($data);

				//記錄日誌
				if ($row['userID']) {
					$log .= "新增用戶${row['userID']}的權限為：";
				} else {
					$groupInfo = M('GroupInfo')->getByGroupid($row['groupID']);
					$log .= "新增群組${groupInfo['groupName']}的權限為：";
				}
				$log .= $this->getAccessDescription($data['level']) . '；';
			} else {
				//記錄日誌
				$accessInfo = M('sysAccessLevel')->field("sys_access_level.userID,group_info.groupName,sys_access_level.level")->join("group_info on sys_access_level.groupID = group_info.groupID")->where("sys_access_level.id=${row['id']}")->find();
				if ($accessInfo['level'] != $data['level']) {
					if ($accessInfo['userID']) {
						$log .= "修改用戶${accessInfo['userID']}的權限為：";
					} else {
						$log .= "修改群組${accessInfo['groupName']}的權限為：";
					}
					$log .= $this->getAccessDescription($data['level']) . '，';
					$log .= '舊權限為：' . $this->getAccessDescription($accessInfo['level']) . '；';
				}

				M('sysAccessLevel')->where("id=${row['id']}")->save(array('level'=>$data['level']));
			}
		}

		SysLogs::log($log);

		$this->success('操作成功');
	}

	/**
     +----------------------------------------------------------
     * 根據權限級別返回描述信息
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	private function getAccessDescription($level)
	{
		$des = array();
		if ($level & 1) $des[] = '可查看';
		if ($level & 2) $des[] = '可新增';
		if ($level & 4) $des[] = '可編輯';
		if ($level & 8) $des[] = '可刪除';
		if ($level & 16) $des[] = '可導出';
		if ($level & 32) $des[] = '可打印';

		return implode('，', $des);
	}

	/**
     +----------------------------------------------------------
     * 取某個模塊的權限列表
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function getAccessRightList()
	{
		$sysID = $_POST['sysID'];

	    $model = M('sysAccessLevel')->join("group_info on group_info.groupID = sys_access_level.groupID");
		$model->join("user on user.userID=sys_access_level.userID");
		$model->field("group_info.groupName,sys_access_level.*,user.firstname,user.lastname,user.nickname");
		$list = $model->where("sysID=$sysID")->order("user.lastname,group_info.groupName")->select();

		foreach ($list as $key => $row) {
			if ($row['userID']) {
				$list[$key]['displayName'] = userDisplayName($row);
			} else {
				$list[$key]['displayName'] = $row['groupName'];
			}
		}
		
		$list = $list ? $list : array();

		$this->success($list);
	}

	/**
     +----------------------------------------------------------
     * 從某個模塊功能中刪除用戶或群組的權限
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function removeAccessRight()
	{
		$ids = $_POST['ids'];

		M('sysAccessLevel')->where("id in ($ids)")->delete();

		$this->success('操作成功');
	}

	/**
     +----------------------------------------------------------
     * 設置模塊是否顯示，管理員不受限制
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function updateAccessRightModule()
	{
		$sysID = $_POST['sysID'];
		$isHidden = $_POST['isHidden'];

		M('sysAccess')->where("sysID = $sysID")->save(array('isHidden'=>$isHidden));

		$this->success('操作成功');
	}
   
}
?>