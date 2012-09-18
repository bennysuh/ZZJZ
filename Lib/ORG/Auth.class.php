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
 * 權限認證類
 +------------------------------------------------------------------------------
 * @author    ccxopen <ccxopen@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */

class Auth
{
	/**
     +----------------------------------------------------------
     * 判斷某用戶是否屬於某個系統群組
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function inGroup($userID, $groupID)
	{
		$groupModel = D('Group');
		return $groupModel->inGroup($userID, $groupID);
	}

	/**
     +----------------------------------------------------------
     * 判斷是否允許執行當前動作
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @param $appGroup 分组
	 * @param $module 模块
	 * @param $action 操作
	 +----------------------------------------------------------
	 * @return boolean 是否有权限
	 +----------------------------------------------------------
     */
	public static function AccessDecision($appGroup = GROUP_NAME, $module = MODULE_NAME, $action = ACTION_NAME)
	{
		if($_SESSION[C('ADMIN_AUTH_KEY')]) return true;//如果是管理員身份
		
		return Auth::checkAccess($appGroup, $module, $action);
	}

	/**
     +----------------------------------------------------------
     * 驗證權限
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @param $appGroup 分组名 
	 * @param $module 模块名
	 * @param $action 操作名
	 +----------------------------------------------------------
	 * @return boolean 是否有权限
	 +----------------------------------------------------------
     */
    private function checkAccess($appGroup = GROUP_NAME, $module = MODULE_NAME, $action = ACTION_NAME)
    {
		$accessGuid = md5($appGroup.$module.$action);

		if (C('USER_AUTH_TYPE') == 1) {
			//非即時數據庫驗證，性能更佳
			//如果此操作之前已經驗證過，根據上一次的結果返回
			if ($_SESSION['ACCESS_LIST'][$accessGuid] === true) return true;
			if ($_SESSION['ACCESS_LIST'][$accessGuid] === false) return false;
		}
		
		//如果不是即時驗證，或之前沒有驗證過此操作，從數據庫中即時驗證
		//USER_AUTH_TYPE=2時，加强验证和即时验证模式 更加安全 后台权限修改可以即时生效，但性能稍差
		$access = M("sysAccessAction")->where("appGroup = '$appGroup' and module = '$module' and action='$action'")->find();
		if (!$access) return true;//此操作並不需要認證

		$requireLevel = $access['level'];
		$sysID = $access['sysID'];
		$userID = $_SESSION[C('USER_AUTH_KEY')];


		//取得当前用户所属的群组
		$groupModel = D('Group');
		$belongToGroups = $groupModel->belongToGroups($userID);
		if (!$belongToGroups) {
			$groups = '-1';
		} else {
			$groups = implode(',', $belongToGroups);
		}

		$count = M('sys_access_level')->where("sysID = $sysID and (userID = '$userID' or groupID in ( $groups )) and ($requireLevel & level) > 0")->count();
		
		if (C('USER_AUTH_TYPE') == 1) {
			//保存本次的驗證結果
			if ($count) {
				$_SESSION['ACCESS_LIST'][$accessGuid] = true;
			} else {
				$_SESSION['ACCESS_LIST'][$accessGuid] = false;
			}
		}

		return $count ? true: false;		     
    }

}
?>