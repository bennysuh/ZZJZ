<?php
/**
 +------------------------------------------------------------------------------
 * 来电记录控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class CallLogAction extends EntryAction {
	/**
	 +----------------------------------------------------------
	 * 根据查询条件显示列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function index() {
		if($_GET['phone'])
		{
			$phone = $_GET['phone'];
			$data["phone"] =  array('like',"%$phone%");
		}
		if($_GET['validFrom']){
			if($_GET['validTo']){
				$validFrom = $_GET['validFrom'];
				$validTo = $_GET['validTo'];
				$validTo .= " 23:59:59";
				$data['callTime'] = array('BETWEEN',"$validFrom,$validTo");			
			}else{
				$validTime = $_GET['validFrom'];
				$data['callTime'] = array('EGT',$validTime);
			}
		}else if($_GET['validTo']){
			$validTime = $_GET['validTo'];
			$data['callTime'] = array('ELT',$validTime);
		}
		if($_GET['updateFrom']){
			if($_GET['updateTo']){
				$updateFrom = $_GET['updateFrom'];
				$updateTo = $_GET['updateTo'];
				$updateTo .= " 23:59:59";
				$data['updateTime'] = array('BETWEEN',"$updateFrom,$updateTo");
			}else{
				$updateFrom = $_GET['updateFrom'];
				$data['updateTime'] = array('EGT',$updateFrom);
			}
		}else if($_GET['updateTo']){
			$updateTo = $_GET['updateTo'];
			$data['updateTime'] = array('ELT',$updateTo);
		}
		import("@.ORG.Page");
		$count = D("CallLogView")->where($data)->count();
		$p = new Page($count, 10);
		$page = $p -> show();
		$result = D("CallLogView")->where($data)->limit($p -> firstRow.','.$p -> listRows)->order("updatetime desc")->select();
		
		foreach ($result as $key => $value) {
			$result[$key]['callTime'] = substr($value['callTime'], 0,-3);
		}
		$this -> assign('page', $page);
		$this->assign("list",$result);
		$this->display();
	}


	/**
	 +----------------------------------------------------------
	 * 更新
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function editCall(){
		$M = D('CallLogView');
		$areaList = M("configCity")->where("nParentId=440100")->getField("nCid,sCn");
		$this->assign("areaList",$areaList);
		$customerPhoneList = M("zz_contact")->field(array('id','fieldA'=>'phone'))->where("type='电话' and tableName='zz_customer'")->select();

		$phoneList = M("zz_phone")->field("id,phone")->select();
		if($phoneList){
			$all = array_merge($customerPhoneList,$phoneList);
		}else{
			$all = $customerPhoneList;
		}
		
		$this->assign("phoneList",json_encode($all));

		if($_GET['logID']){
			$this->assign("customerID",($all));
			$result = $M->where("zz_calllog.id=" . $_GET['logID'])->find();
			$this->assign("phoneID",$_GET["phoneID"]);
			$this->assign("logID",$_GET["logID"]);
			$this->assign("name",$result['name']);
			$this->assign("phone",$result['phone']);
			$this->assign("callTime",$result['callTime']);
			$this->assign("type",$result['type']);
			$this->assign("area",$result['areaID']);
			$this->assign("process",$result['process']);
			$this->assign("detail",$result['detail']);
			$this->display();
		}else if($_GET['phoneID']){
			$result = $M->where('zz_phone.id='.$_GET['phoneID'])->find();
			$this->assign("name",$result['name']);
			$this->assign("phone",$result['phone']);
			$this->assign("phoneID",$result['phoneID']);
			$this->assign("area",$result['areaID']);
			$this->display();
		}else{
			$this->display();
		}
	}
	
	
	public function saveCallLog(){
		$M = D('CallLogView');
		$phoneData['id']= $_POST['phoneID'];
		$phoneData['phone']= $_POST['phone'];
		$phoneData['name'] = $_POST['name'];
		$phoneData['areaID'] = $_POST['areaID'];
		$phoneData['customerID'] = D("Customer")->getCustomerByPhone($_POST['phone']);
		$result = M("zz_phone")->data($phoneData)->save();

		if(is_int($result)){
			$callData['phoneID'] = $_POST['phoneID'];
			$callData['id'] = $_POST['logID'];
			$callData['type'] = $_POST['type'];
			$callData['detail'] = $_POST['detail'];
			$callData['callTime'] = $_POST['callTime'];
			$callData['process'] = $_POST['process'];
			$result = M('zzCalllog')->data($callData)->save();
			if(is_int($result)){
				SysLogs::log("保存来电记录,calllogid=" . $result);
				$logData["tablename"] = "zz_calllog";
				$logData["no"] = $callData['id'];
				$logData["operate"] = "update";
				$logData["updateUser"] = $_SESSION['loginName'];
				ZZLogModel::updateLog($logData);
				$this->success("保存成功");
			}else{
				
				$this -> error('保存来电记录失敗');
			}
		}else{
			$this -> error('保存来电人员失敗');
		}
	}
	
	public function addCallLog(){
		$phoneData['phone']= $_POST['phone'];
		$phoneData['name'] = $_POST['name'];
		$phoneData['areaID'] = $_POST['areaID'];
		$customerID = D("Customer")->getCustomerByPhone($_POST['phone']);
		$phoneData['customerID'] = $customerID;

		$M = M("zz_phone");
		if($_POST['phoneID']){
			$phoneData['id'] = $_POST['phoneID'];
			$result = $M->data($phoneData)->save();
		}else{
			$exist = $M->where("phone=" . $_POST['phone'])->find();
			if($exist){
				$result =  M("zz_phone")->data($phoneData)->where("phone=" . $_POST['phone'] )->save();
			}else{
				$result =  M("zz_phone")->add($phoneData);
			}		
		}
		if(is_int($result)){
			if($_POST['phoneID']){
				$callData['phoneID'] = (int)$_POST['phoneID'];
			}else{
				$callData['phoneID'] = (int)$result;
			}
			$callData['type'] = $_POST['type'];
			$callData['detail'] = $_POST['detail'];
			$callData['callTime'] = $_POST['callTime'];
			$callData['process'] = $_POST['process'];
			$result = M('zzCalllog')->data($callData)->add();
			if($result){
				SysLogs::log("新增来电记录,calllogid=" . $result);
				$logData["tablename"] = "zz_calllog";
				$logData["no"] = $result;
				$logData["operate"] = "update";
				$logData["updateUser"] = $_SESSION['loginName'];
				ZZLogModel::addLog($logData);
				$this->success("新增成功");
			}else{
				$this -> error('新增来电记录失敗');
			}
		}else{
			$this -> error('新增来电人员失敗');
		}
	}
	
	/**
	 +----------------------------------------------------------
	 * 刪除
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function removeCallLog() {
		$id = $_POST["id"];
		if ($id) {
			$result = M('zz_calllog') -> where("id=" . $id) -> delete();
			SysLogs::log("删除来电记录,id=" . $id);
			$logData["tablename"] = "zz_calllog";
			$logData["no"] = $id;
			$logData["operate"] = "delete";
			$logData["updateUser"] = $_SESSION['loginName'];
			ZZLogModel::updateLog($logData);
			if(is_int($result))
				$this -> success("删除成功");
			else 
				$this -> error('删除失敗');
		} else {
			$this -> error('无参数');
		}
	}
	
	/**
	 +----------------------------------------------------------
	 * 某个电话的所有记录
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function phoneLog() {
		$id = $_GET["phoneID"];
		if ($id) {
			import("@.ORG.Page");
			if($_GET['validFrom']){
				if($_GET['validTo']){
					$validFrom = $_GET['validFrom'];
					$validTo = $_GET['validTo'];
					$data['callTime'] = array('BETWEEN',"$validFrom,$validTo");
				}else{
					$validTime = $_GET['validFrom'];
					$data['callTime'] = array('EGT',$validTime);
				}
			}else if($_GET['validTo']){
				$validTime = $_GET['validTo'];
				$data['callTime'] = array('ELT',$validTime);
			}
			$M = D("CallLogView");
			$data['phoneID'] = $id;
			$count =$M->where($data)->count();
			$p = new Page($count, 10);
			$page = $p -> show();
			$result = $M->where($data)->limit($p -> firstRow.','.$p -> listRows)->order("updatetime desc")->select();
			foreach ($result as $key => $value) {
				$result[$key]['callTime'] = substr($value['callTime'], 0,-3);
			}
			$this->assign("phoneID",$id);
			$this->assign("name",$_GET["name"]);
			$this->assign("phone",$_GET["phone"]);
			$this->assign("area",$_GET["area"]);
			$this -> assign('page', $page);
			$this->assign("list",$result);
			$this->display();
		} else {
			$this -> error('无参数');
		}
	}
}
?>