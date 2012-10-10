<?php
if (!defined('THINK_PATH')) exit();
return array(
	/*=================環境無關配置===================*/
	'DB_TYPE'=>'mysql',
	'DB_PORT'=>'3306',
	'DB_PREFIX'=>'',
	'DB_CHARSET' => 'utf8',
	'URL_MODEL' => 1,
	'VERIFY_CODE_MODE' => false, // 是否验证码模式
	'LANG_AUTO_DETECT'   =>  false,     // 自动侦测语言
	'VAR_LANGUAGE'=>'locale',
	'APP_GROUP_LIST'=> 'Admin,Portal',// 分组列表
	'DEFAULT_GROUP' => 'Portal',// 默认分组
	'JUMP_TO_MODULE' => __ROOT__.'/Admin/Staff/index',// 登录后自动跳转的模块
	'TMPL_ACTION_ERROR' => './Tpl/Public/error.html',// 错误跳转页面
	'TMPL_ACTION_SUCCESS' => './Tpl/Public/success.html',// 成功跳转页面
	'OUTPUT_ENCODE'=>false,
	'ENABLE_EDB' => false,

	//調試狀態配置
	'APP_DEBUG' => true,
	'LOG_RECORD' => true,
	'LOG_EXCEPTION_RECORD'  => true,
	'LOG_LEVEL' => 'EMERG,ALERT,CRIT,ERR,WARN,NOTICE,DEBUG,SQL',
	'SHOW_ERROR_MSG' => true,




	//系統變量
	'TOKEN_ON' => false,
	'VAR_PAGE' => 'p',
	'ADMIN_AUTH_KEY' => 'admin',
	'USER_AUTH_KEY' => 'user',
	'USER_AUTH_TYPE' => 2,// 1表示從上一次的驗證緩存中取結果，2表示每次都從數據庫即時驗證，無需再登錄，權限立即生效

	/*=================系統敏感配置，應與數據庫的信息保持關聯===================*/

	'SYS_USER_GROUP_ID' => 2,//系統合法用戶組的ID號
		

	/*=================環境差異配置===================*/

	//數據庫
	'DB_HOST'=>'106.187.47.22',
	'DB_NAME'=>'uu143072',
	'DB_USER'=>'uu143072',
	'DB_PWD'=>'fNLSxc0Y',
);
?>