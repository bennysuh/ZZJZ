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
 * 系统日志类
 +------------------------------------------------------------------------------
 * @author    ccxopen <ccxopen@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */

class SysLogs
{
	//日誌實例
	private static $_instance;

	//格式化設置
	private $format;

	//日誌時間
	private $time;

	//操作人
	private $userID;
	
	/**
     +----------------------------------------------------------
     * 日誌類構造函數
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function __construct() {
		$this->initFormat();
		$this->time = date('Y-m-d H:i:s');
		$this->userID = $_SESSION[C('USER_AUTH_KEY')];
	}

	/**
     +----------------------------------------------------------
     * 日誌格式初始化
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function initFormat() {
		$this->format = array();
		$this->format['Admin_Auth_login'] = '%username%在%time%登入系統';
		$this->format['Admin_Auth_logout'] = '%username%在%time%退出系統';
		$this->format['Admin_Photo_downloadGateway'] = '%username%在%time%下載圖片，編號為[%photoID%]';
		$this->format['Admin_Dictionary_updateNicknames'] = '%username%在%time%更新了同義詞';
		$this->format['Admin_Dictionary_importSection'] = '%username%在%time%更新了%classID%%subClassID%欄目';
		$this->format['Admin_Feature_removePhoto'] = '%username%在%time%刪除了圖片，編號為%photoID%';
		$this->format['Admin_User_addUser'] = '%username%在%time%增加了用戶%newUser%';
		$this->format['Admin_User_removeUser'] = '%username%在%time%刪除了用戶%userID%';
		$this->format['Admin_User_saveUserInfo'] = '%username%在%time%更新了用戶%userID%的基本資料';
	}
	
	/**
     +----------------------------------------------------------
     * 初始化日誌實例
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	static private function getInstance() {
		if (!isset(self::$_instance)) {
			self::$_instance = new SysLogs();
		}
		return self::$_instance;
	}
	
	/**
     +----------------------------------------------------------
     * 记录系统日志
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public static function log($description = '')
	{
		$instance = self::getInstance();
		if (empty($description)) {
			$instance->quickLog();
		} else {
			$instance->write($instance->userID . '在' . $instance->time . $description);
		}
	}

	/**
     +----------------------------------------------------------
     * 记录系统日志
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	private function quickLog()
	{
		$themeKey = GROUP_NAME.'_'.MODULE_NAME.'_'.ACTION_NAME;
		$theme = $this->format[$themeKey];
		if (!$theme) return;

		switch ($themeKey) {
		case "Admin_Auth_login":
			$this->userID = $_POST['username'];
			$description = str_replace(array('%username%', '%time%'),array($this->userID, $this->time),$theme);
			break;
		case "Admin_Auth_logout":
			$description = str_replace(array('%username%', '%time%'),array($this->userID, $this->time),$theme);
			break;
		case "Admin_User_addUser":
			$description = str_replace(array('%username%', '%time%', '%newUser%'),array($this->userID, $this->time, $_REQUEST['userID']),$theme);
			break;
		case "Admin_User_removeUser":
			$description = str_replace(array('%username%', '%time%', '%userID%'),array($this->userID, $this->time, $_REQUEST['userID']),$theme);
			break;
		case "Admin_User_saveUserInfo":
			$description = str_replace(array('%username%', '%time%', '%userID%'),array($this->userID, $this->time, $_REQUEST['userID']),$theme);
			break;
		}

		$this->write($description);		
	}

	private function write($description)
	{
		$data['userID'] = $this->userID;
		$data['appGroup'] = GROUP_NAME;
		$data['module'] = MODULE_NAME;
		$data['action'] = ACTION_NAME;
		$data['dateTime'] = $this->time;
		$data['description'] = $description;
		$data['url'] = $_SERVER['REQUEST_URI'];
		$data['requestData'] = json_encode($_REQUEST);

		M('sysLogs')->add($data);
	}

	
	
}
?>