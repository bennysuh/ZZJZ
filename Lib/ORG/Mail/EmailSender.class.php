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
 * 邮件发送类
 +------------------------------------------------------------------------------
 * @author    ccxopen <ccxopen@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */

import('@.ORG.Mail.PHPMailer');

//邮件处理类
class EmailSender
{
	//邮件发送提供者
	private $mail;

	//暫存收件人
	private $toAddress;


	/**
     +----------------------------------------------------------
     * 构造函数
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function __construct()
	{
		$this->mail = new PHPMailer();//建立邮件发送类

		$this->mail->IsSMTP();//使用SMTP方式发送
		$this->mail->IsHTML();
		$this->mail->Host = C('SMTP'); //设置SMTP服务器
		$this->mail->SMTPAuth = true;//启用SMTP验证功能
		$this->mail->CharSet = "UTF-8";
		//$this->mail->AltBody = "To view the message, please use an HTML compatible email viewer!";
		$this->mail->Username = C("EMAIL_ACCOUNT");//发送帐号
		$this->mail->Password = C("EMAIL_PASSWORD"); //密码		
		$this->mail->From = C('SEND_FROM'); //发件人E-mail地址
		$this->mail->FromName = C('SEND_FROMNAME');   //发件人称呼
		
		$this->toAddress = array();
	}

	/**
     +----------------------------------------------------------
     * 设置标题
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function setSubject($subject)
	{
		//$this->mail->Subject = $subject.'--'.date('Y/m/d H:i:s');
		$this->mail->Subject = $subject;
	}

	/**
     +----------------------------------------------------------
     * 设置正文
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function setBody($body)
	{
		$this->mail->Body = $body;
	}

	/**
     +----------------------------------------------------------
     * 增加收件人
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function AddAddress($email, $name)
	{
		$email = trim(filter_var($email, FILTER_SANITIZE_EMAIL));
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) return;
		$this->toAddress[] = array($email, $name);
	}

	/**
     +----------------------------------------------------------
     * 发送邮件
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function Send()
	{
		if (C('APP_DEBUG')) {
			$to = C('TESTING_EMAIL');
		} else {
			$to = $this->toAddress;
			$this->toAddress = array();
		}
		Log::write("send email");
		foreach ($to as $email) {
			$this->mail->AddAddress($email[0], $email[1]);
			Log::write($email[0] . "," . $email[1]);
		}
		$result = $this->mail->Send();
		
		Log::write($this->mail->ErrorInfo);
		return $result;
	}

}






?>