<?php
/**
 +------------------------------------------------------------------------------
 * EMAIL model控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class EmailAction extends EntryAction {
	/**
	 +----------------------------------------------------------
	 * 根据查询条件显示列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function index() {
		if ($_GET['title']) {
			$title = $_GET['title'];
			$where['content'] = array('like', "%$title%");
			$where['title'] = array('like', "%$title%");
			$where['_logic'] = 'or';
			$data['_complex'] = $where;
		}
		if ($_GET['sender']) {
			$sender = $_GET['sender'];
			$data['sender'] = array('like', "%$sender%");
		}
		if($_GET['sendFrom']){
			if($_GET['sendTo']){
				$validFrom = $_GET['sendFrom'];
				$validTo = $_GET['sendTo'];
				$validTo .= " 23:59:59";
				$data['updateTime'] = array('BETWEEN',"$validFrom,$validTo");			
			}else{
				$validTime = $_GET['sendFrom'];
				$data['updateTime'] = array('EGT',$validTime);
			}
		}else if($_GET['sendTo']){
			$validTime = $_GET['sendTo'];
			$data['updateTime'] = array('ELT',$validTime);
		}
		
		$M = M("zz_email");
		import("@.ORG.Page");
		$count = $M -> where($data) -> count();
		$p = new Page($count, 10);
		$page = $p -> show();
		$result = $M ->field("id,title,content,sender,user,updateTime")-> where($data) -> limit($p -> firstRow . ',' . $p -> listRows) -> order("updateTime desc") -> select();

		$this -> assign('page', $page);
		$this -> assign("list", $result);
		$this -> display();
	}

	/**
	 +----------------------------------------------------------
	 * 新增
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function sendEmail() {
		//是否已經存在於本地數據庫
		$result = D("Customer") -> getCustomersForEmail();
		$this -> assign("senderList", json_encode($result));
		$modelTypeList = M('zz_emailmodel')->getField("id,modelTitle");
		$this->assign("modelTypeList",$modelTypeList);
		$this -> display();
	}

	/**
	 +----------------------------------------------------------
	 * 显示新增/编辑信息
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function editEmail() {
		$id = $_GET['id'];
		//已有记录

		if ($id) {//assess列表过来的
			$M = M("zz_email");
			$result = $M -> where("id=" . $id) -> find();
			if ($result) {
				$this -> assign("id", $id);
				$this -> assign("title", $result['title']);
				$this -> assign("content", $result['content']);
				$this -> assign("sender", $result['sender']);
				$this -> display();
			} else {
				$this -> error("无此Email模板");
			}
		} else {
			$this -> display();
		}
	}

	/**
	 +----------------------------------------------------------
	 * 发送邮件
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function addEmail() {
		set_time_limit(0);
		$M = M('zz_email');
		$data = $M -> create();
		if ($data) {
			unset($data['textContent']);
			import('@.ORG.Mail.EmailSender');
			$mail = new EmailSender();
			//建立邮件发送类
			$mail -> setSubject($data['title']);
			//标题
			$mail -> setBody($data['content']);
			$toMailUser = explode(",", $data['sender']);
			//添加地址
			foreach ($toMailUser as $user) {
				$mail -> AddAddress(substr($user, strpos($user, "(")+1,-1), substr($user,0,strpos($user, "(")));
			}
			
			if ($toMailUser){
				$emaiFlag = $mail -> send();
			}
			//发送成功
			if ($emaiFlag) {
				$data['user'] = $_SESSION['loginName'];
				$result = $M -> data($data) -> add();
				if ($result) {
					SysLogs::log("发送email,id=" . $data["id"]);
					$logData["tablename"] = "zz_email";
					$logData["no"] = $data["id"];
					$logData["operate"] = "update";
					$logData["updateUser"] = $_SESSION['loginName'];
					ZZLogModel::updateLog($logData);
					$this -> success("发送成功");
				} else {
					$this -> error("保存失败！");
				}
			}else{
				$this -> error('发送失败');
			}
		} else {
			$this -> error('保存失敗');
		}
	}

	/**
	 +----------------------------------------------------------
	 * 刪除
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function removeEmail() {
		$id = $_POST["id"];
		if ($id) {
			$result = M('zz_email') -> where("id=" . $id) -> delete();
			SysLogs::log("删除zz_email,id=" . $id);
			$logData["tablename"] = "zz_email";
			$logData["no"] = $id;
			$logData["operate"] = "delete";
			$logData["updateUser"] = $_SESSION['loginName'];
			ZZLogModel::updateLog($logData);
			if (is_int($result))
				$this -> success("删除成功");
			else
				$this -> error('删除失敗');
		} else {
			$this -> error('无参数');
		}
	}
	
	/**
	 +----------------------------------------------------------
	 * 查看
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function viewEmail()
	{
		$id = $_GET['id'];
		if ($id) {
			$M = M("zz_email");
			$result = $M -> where("id=" . $id) -> find();
			if ($result) {
				$this -> assign("id", $id);
				$this -> assign("title", $result['title']);
				$this -> assign("content", $result['content']);
				$this -> assign("sender", $result['sender']);
				$this -> assign("updateTime", $result['updateTime']);
				$this -> assign("user", $result['user']);
				$this -> display();
			} else {
				$this -> error("无此Email");
			}
		} else {
			$this -> error("no params!");
		}
	}

}
?>