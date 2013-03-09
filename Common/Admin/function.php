<?php

function objectToArray($d) {
	if (is_object($d)) {
		// Gets the properties of the given object
		// with get_object_vars function
		$d = get_object_vars($d);
	}

	if (is_array($d)) {
		/*
		 * Return array converted to object
		 * Using __FUNCTION__ (Magic constant)
		 * for recursive call
		 */
		return array_map(__FUNCTION__, $d);
	} else {
		// Return array
		return $d;
	}
}

/**
 +----------------------------------------------------------
 * 數組轉換成對象
 +----------------------------------------------------------
 * @access public
 +----------------------------------------------------------
 * @param array / object
 +----------------------------------------------------------
 * @return object
 +----------------------------------------------------------
 */
function arrayToObject($d) {
	if (is_array($d)) {
		/*
		 * Return array converted to object
		 * Using __FUNCTION__ (Magic constant)
		 * for recursive call
		 */
		return (object) array_map(__FUNCTION__, $d);
	} else {
		// Return object
		return $d;
	}
}

/**
 +----------------------------------------------------------
 * 返回用户登录信息
 +----------------------------------------------------------
 * @access public
 +----------------------------------------------------------
 * @param array $user 用户变量
 +----------------------------------------------------------
 * @return String 用户信息
 +----------------------------------------------------------
 */
function userDisplayName($user) {
	return $user['lastname'] . ',' . $user['firstname'] . ($user['nickname'] ? " (${user['nickname']})" : '');
}

/**
 +----------------------------------------------------------
 * 判斷是否有訪問URL的權限
 +----------------------------------------------------------
 * @access public
 +----------------------------------------------------------
 * @param string $url URL
 +----------------------------------------------------------
 * @return boolean 是否擁有訪問權限
 +----------------------------------------------------------
 */
function checkAuthByUrl($url) {
	// �������顢ģ��Ͳ���
	$url = trim($url, '/');
	$path = explode('/', $url);

	$action = array_pop($path);
	$module = array_pop($path);
	$appGroup = array_pop($path);

	import("@.ORG.Auth");
	return Auth::AccessDecision($appGroup, $module, $action);
}

/**
 +----------------------------------------------------------
 * 創建菜單
 +----------------------------------------------------------
 * @access public
 +----------------------------------------------------------
 * @param $modules
 +----------------------------------------------------------
 * @return array 菜單集合
 +----------------------------------------------------------
 */
function createMenu($modules) {
	$menus = array();
	foreach ($modules as $key => $row) {
		$items = $row['items'];
		if (!$items)
			continue;

		$displayItems = array();
		foreach ($items as $item) {
			if (!checkAuthByUrl($item['url']))
				continue;
			$dependence = $item['dependence'];
			if (!$dependence) {
				$displayItems[] = $item;
				continue;
			}
			$accept = true;
			foreach ($dependence as $depends) {
				if (!checkAuthByUrl($depends)) {
					$accept = false;
					break;
				}
			}
			if ($accept) {
				$displayItems[] = $item;
			}
		}

		if (!$displayItems)
			continue;

		$menus[$key]['items'] = $displayItems;
		$menus[$key]['logo'] = $row['logo'];
		$menus[$key]['height'] = 26 + 27 * count($displayItems);
	}
	return $menus;
}

function microtime_format() {
	$time = number_format(microtime(true), 8, '.', '');
	return explode(".", $time);
}

function htmlspecialcharsx($str) {
	$s = htmlspecialchars($str);
	return str_replace('&amp;#', '&#', $s);
}

/**
 * SHOW COLUMNS FROM zz_staff LIKE  'yslevel'
 *
 * 从数据库中的enum元素中组装成数组返回
 */
function getEnumAtrr($result) {
	$enumArr = $result[0];
	$enum_type = $enumArr['Type'];
	$enumStr = strstr(strstr($enum_type, "'"), ")", true);
	$enumStr = str_replace("'", "", $enumStr);
	$enumStr = explode(",", $enumStr);
	return $enumStr;
}

function timetostring($time, $full = false) {
	$time = ($time >= 31536000 ? floor($time / 31536000) . ($full ? " year" . (floor($time / 31536000) > 1 ? "s" : "") : "y") . ", " : "") . ($time % 31536000 >= 86400 ? floor($time % 31536000 / 86400) . ($full ? " day" . (floor($time % 31536000 / 86400) > 1 ? "s" : "") : "d") . ", " : "") . ($time % 31536000 % 86400 >= 3600 ? floor($time % 31536000 % 86400 / 3600) . ($full ? " hour" . (floor($time % 31536000 % 86400 / 3600) > 1 ? "s" : "") : "h") . ", " : "") . ($time % 31536000 % 86400 % 3600 >= 60 ? floor($time % 31536000 % 86400 % 3600 / 60) . ($full ? " minute" . (floor($time % 31536000 % 86400 % 3600 / 60) > 1 ? "s" : "") : "m") . ", " : "") . ($time % 31536000 % 86400 % 3600 % 60 > 0 ? ($time % 31536000 % 86400 % 3600 % 60) . ($full ? " second" . (floor($time % 31536000 % 86400 % 3600 % 60) > 1 ? "s" : "") : "s") . ", " : "");
	return substr($time, 0, strlen($time) - 2);
}
?>