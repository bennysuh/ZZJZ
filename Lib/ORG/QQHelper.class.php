<?php
// +----------------------------------------------------------------------
// | Driving school management system [ DSMS ]
// +----------------------------------------------------------------------
// | Copyright (c) 2012 MarkDream.com All rights reserved.
// +----------------------------------------------------------------------
// | Link ( http://www.markdream.com )
// +----------------------------------------------------------------------
// | Author: Jxcent <jxcent@gmail.com>
// +----------------------------------------------------------------------
// $Id: QQHelper.class.php	 2012-9-2 下午04:43:22Z	Jxcent $

/**
 * 此类QQ互联类，负责获取用户的openid 和 访问令牌
 * openid 相当于QQ号  一个QQ号对应唯一一个openid
 * 访问令牌 +获取当前应用+openid来实现某种操作 例如分享、发说说、传图……具体可以参考
 * http://wiki.opensns.qq.com/wiki/%E3%80%90QQ%E7%99%BB%E5%BD%95%E3%80%91%E6%96%87%E6%A1%A3%E8%B5%84%E6%BA%90
 * @author Jxcent
 *
 */
class QQHelper {
	//QQ登录
	public function login($appid, $scope, $callback) {
		$_SESSION[SES_STATE_NAME] = md5(uniqid(rand(), true));
		//CSRF protection
		$login_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=" . $appid . "&redirect_uri=" . urlencode($callback) . "&state=" . $_SESSION[SES_STATE_NAME] . "&scope=" . $scope;
		header("Location:$login_url");
	}

	//登录成功回调函数 目的就是获取访问令牌
	public function callback($path) {
		dump($_REQUEST['state'] . "," .  $_SESSION[SES_STATE_NAME]);
		if ($_REQUEST['state'] == $_SESSION[SES_STATE_NAME]) {
			$token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&" . "client_id=" . APP_ID . "&redirect_uri=" . urlencode($path) . "&client_secret=" . APP_KEY . "&code=" . $_REQUEST["code"];

			$response = get_url_contents($token_url);
			if (strpos($response, "callback") !== false) {
				$lpos = strpos($response, "(");
				$rpos = strrpos($response, ")");
				$response = substr($response, $lpos + 1, $rpos - $lpos - 1);
				$msg = json_decode($response);
				if (isset($msg -> error)) {
					echo "<h3>错误代码:</h3>" . $msg -> error;
					echo "<h3>信息  :</h3>" . $msg -> error_description;
					exit();
				}
			}

			$params = array();
			parse_str($response, $params);
			$_SESSION[SES_TOKEN_NAME] = $params["access_token"];

		} else {
			echo("The state does not match. You may be a victim of CSRF.");
		}
	}

	//获取该QQ用户的openid
	public function get_openid() {
		$graph_url = "https://graph.qq.com/oauth2.0/me?access_token=" . $_SESSION[SES_TOKEN_NAME];

		$str = get_url_contents($graph_url);
		if (strpos($str, "callback") !== false) {
			$lpos = strpos($str, "(");
			$rpos = strrpos($str, ")");
			$str = substr($str, $lpos + 1, $rpos - $lpos - 1);
		}

		$user = json_decode($str);
		if (isset($user -> error)) {
			echo "<h3>错误代码:</h3>" . $user -> error;
			echo "<h3>信息  :</h3>" . $user -> error_description;
			exit();
		}
		$_SESSION[SES_OPENID_NAME] = $user -> openid;
	}

	//获取用户信息
	public function get_user_info() {
		$get_user_info = "https://graph.qq.com/user/get_user_info?" . "access_token=" . $_SESSION[SES_TOKEN_NAME] . "&oauth_consumer_key=" . APP_ID . "&openid=" . $_SESSION[SES_OPENID_NAME] . "&format=json";

		return get_url_contents($get_user_info);
	}

}
