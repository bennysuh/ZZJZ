<?php
if (!defined('THINK_PATH')) exit();
define ( 'WEBSITE_URL', 'www.gzzzjz.com' );
//应用ID 这个是在  http://connect.qq.com/   申请
define ( 'APP_ID', 100312313 );
//应用KEY  这个是在  http://connect.qq.com/   申请
define ( 'APP_KEY', '7dc0c07dd9cbbf839940e91da15866e5' );
//QQ登录回调地址
define ( 'APP_LOGIN_CPATH', WEBSITE_URL . "/ZZJZ/Portal/Login/qq_login_cb" );
//绑定QQ回调地址
define ( 'APP_BIND_CPATH', WEBSITE_URL . '/ZZJZ/Portal/Main/qq_bc' );
//请求用户授权时向用户显示的可进行授权的列表
define ( 'APP_SCOPE', 'get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo' );
//图片是否本地化
define ( 'AVATAR_LOCAL', false );

//Session设置
define ( 'SES_TOKEN_NAME', 'qq_token' ); //token名称
define ( 'SES_OPENID_NAME', 'qq_openid' ); //QQ用户的openid名称
define ( 'SES_STATE_NAME', 'qq_state' );// 状态名称
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
	'APP_GROUP_LIST'=> 'Admin,Portal,Api',// 分组列表
	'DEFAULT_GROUP' => 'Portal',// 默认分组
	'JUMP_TO_MODULE' => __ROOT__.'/Portal/Index',// 登录后自动跳转的模块
	'TMPL_ACTION_ERROR' => './Tpl/Public/error.html',// 错误跳转页面
	'TMPL_ACTION_SUCCESS' => './Tpl/Public/success.html',// 成功跳转页面
	'OUTPUT_ENCODE'=>false,
	'ENABLE_EDB' => false,

	//調試狀態配置
	'APP_DEBUG' => false,
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
	//郵件
	// 'SMTP' => 'smtp.qq.com',
	// 'SEND_FROM' => '1275653073@qq.com',
	// 'TESTING_EMAIL' => array(
	    // array('42613823@qq.com','davidhuang'),
	// ),
	// 'EMAIL_ACCOUNT'=>"1275653073@qq.com",
	// 'EMAIL_PASSWORD'=>"89285610",
	// 'SEND_FROMNAME'=>'广州市真真家政服务有限公司',
	'SMTP' => 'smtp.163.com',
	'SEND_FROM' => 'lhdst@163.com',
	'TESTING_EMAIL' => array(
		array('lhdst@163.com','davidhuang'),
	),
	'EMAIL_ACCOUNT'=>"lhdst@163.com",
	'EMAIL_PASSWORD'=>"xqh40510832229",
	'SEND_FROMNAME'=>'广州市真真家政服务有限公司',
	
	
	
	//數據庫
	'DB_HOST'=>'localhost',
	'DB_NAME'=>'uu143072',
	'DB_USER'=>'root',
	'DB_PWD'=>'',
	
	//SMS
	'SMS_USERID'=>'044843',
	'SMS_PASSWORD'=>'38295611',
	'SMS_ACCOUNT'=>'admin',
	'WEB_HOST'=>'localhost/',
	'STORAGE_ROOT'=>'/Uploads/prolactin/',
);
?>