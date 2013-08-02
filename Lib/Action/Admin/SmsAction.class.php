<?php
/**
 +------------------------------------------------------------------------------
 * SMS管理控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class SmsAction extends EntryAction {
	/**
	 +----------------------------------------------------------
	 * 根据查询条件显示列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function index() {
		if ($_GET['smsContent']) {
			$content = $_GET['smsContent'];
			$data['smsContent'] =  array('like', "%$content%"); ;
		}
		if ($_GET['sender']) {
			$sender = $_GET['sender'];
			$data['sender'] = array('like', "%$sender%");
		}
		if($_GET['sendNumber']){
			$sendNumber = $_GET['sendNumber'];
			$data['sendNumber'] = array('like', "%$sendNumber%");
		}
		if($_GET['validFrom']){
			if($_GET['validTo']){
				$validFrom = $_GET['validFrom'];
				$validTo = $_GET['validTo'];
				$validTo .= " 23:59:59";
				$data['updateTime'] = array('BETWEEN',"$validFrom,$validTo");	
						
			}else{
				$validTime = $_GET['validFrom'];
				$data['updateTime'] = array('EGT',$validTime);
			}
		}else if($_GET['validTo']){
			$validTime = $_GET['validTo'];
			$data['updateTime'] = array('ELT',$validTime);
		}
		
		
		$M = M("zz_smslog");
		import("@.ORG.Page");
		$count = $M -> where($data) -> count();
		$p = new Page($count, 10);
		$page = $p -> show();
		$result = $M ->field("sendNumber,smsContent,sender,updateTime")-> where($data) -> limit($p -> firstRow . ',' . $p -> listRows) -> order("updateTime desc") -> select();

		$this -> assign('page', $page);
		$this -> assign("list", $result);
		$this -> display();
	}
	
	/**
	 +----------------------------------------------------------
	 * 发送信息
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function sendSms()
	{
		$M = D('CallLogView');
		$areaList = M("configCity")->where("nParentId=440100")->getField("nCid,sCn");
		$this->assign("areaList",$areaList);
		$customerPhoneList = D("Customer")->getCustomerNameAndPhone();
		$phoneList = M("zz_phone")->field("id,phone,name")->select();
		foreach ($customerPhoneList as $key => $value) {
			$customerPhoneList[$key]['type'] = "customer";
		}
		foreach ($phoneList as $key => $value) {
			$phoneList[$key]['type'] = "phone";
		}
		if($phoneList){
			$all = array_merge($customerPhoneList,$phoneList);
		}else{
			$all = $customerPhoneList;
		}
		$this->assign("senderList",json_encode($all));
		$this->display();
	}
	
	/**
	 +----------------------------------------------------------
	 * 发送短信
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function addSms(){
		$M = M('zz_smslog');
		$data = $M -> create();
		if ($data) {
			$senderStr = $data['sender'];
			$array = explode(",", $senderStr);
			$senderNumber = "";
			$smsData = array();
			$smsID = date("YmdHis");
			foreach ($array as $key => $value) {
				$sender = substr($value, 0,strpos($value, "("));
				$sendNumber = substr($value, strpos($value,"(")+1,-1);
				$smsData[$key]['sender'] = $sender;
				$smsData[$key]['sendNumber'] = $sendNumber;
				$smsData[$key]['smsContent'] = $data["smsContent"];
				$smsData[$key]['smsID'] = $smsID;
			}
			// $result = $M->addAll($smsData);
			// if($result){
				// $this->success("发送成功");
			// }else{
				// $this->error("保存到数据库失败");
			// }
			$this->test();
		} else {
			$this -> error('保存失敗');
		}
	}
	
	public function test()
	{
		import("@.ORG.Sms");
		$sms = new Sms();
		$numbers = array();
		$numbers[] = "18925117804";
		$content = "新年快乐";
		$sms->sendSms($numbers, $content);
	}
}
?>