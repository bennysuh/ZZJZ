<?php
    /**
	 +------------------------------------------------------------------------------
	 * 合同控制類
	 +------------------------------------------------------------------------------
	 * @author    david <lhdst@163.com>
	 * @version   $Id$
	 +------------------------------------------------------------------------------
	 */
class ContractAction extends EntryAction {
	/**
	 +----------------------------------------------------------
	 * 根据查询条件显示列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function index() {
		
		//根据URL参数查询
		import("@.ORG.Page");
		//导入分页类
		$M = D("ContractView");
		//获取查询参数
		if($_GET['bh']){
			$keyword = $_GET['bh'];
			$data['bh'] = array('like',"%$keyword%");
		}
		if($_GET['customerName']){
			$keyword = $_GET['customerName'];
			$data['customerName'] = array('like',"%$keyword%");
		}
		if($_GET['staffName']){
			$keyword = $_GET['staffName'];
			$data['staffName'] = array('like',"%$keyword%");
		}
		if($_GET['startDate']){
			$keyword = $_GET['startDate'];
			$data['orderDate'] = array('EGT',"$keyword");
		}
		if($_GET['endDate']){
			$keyword = $_GET['endDate'];
			if($_GET['startDate']){
				$startDate = $_GET['startDate'];
				$data['orderDate'] = array('BETWEEN',"$startDate,$keyword");
			}else{
				$data['orderDate'] = array('ELT',"$keyword");
			}
		}
		$data['isShow'] = 1;
		$count = $M->where($data)->count();
		Log::write(M()->getLastSql());
		$p = new Page($count, 10);
		$page = $p -> show();
		$list = $M->where($data)->limit($p -> firstRow.','.$p -> listRows)->order("id desc")->select();
		$this -> assign('page', $page);
		$this -> assign('list', $list);
		$this -> display();
	}
	
	/**
	 +----------------------------------------------------------
	 * 显示编辑信息
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function editContract()
	{
		$id = $_GET["id"];
		$staffList = D("Staff")->getStafflist();
		$this->assign("staffList",json_encode($staffList));
		$customerList = D("Customer")->getCustomerlist();
		$this->assign("customerList",json_encode($customerList));
		//编辑页面
		if ($id) {
			$M = D('ContractView');
			$info = $M -> where("zz_contract.id = " . $id) -> find();
			$this->assign("id",$id);
			$this->assign("bh",$info['bh']);
			$this->assign("customerId",$info['customerId']);
			$this->assign("customerName",$info['customerName']);
			$this->assign("staffName",$info['staffName']);
			$this->assign("hospital",$info['hospital']);
			$this->assign("staffId",$info['staffId']);
			$this->assign("ysLevel",$info['ysLevel']);
			$this->assign("alternate1",$info['alternate1']);
			$this->assign("alternate2",$info['alternate2']);
			$this->assign("birthday",$info['birthday']);
			$this->assign("expectedDay",$info['expectedDay']);
			$this->assign("address",$info['address']);
			$this->assign("startDate",$info['startDate']);
			$this->assign("endDate",$info['endDate']);
			$this->assign("orderDate",$info['orderDate']);
			$this->assign("price",$info['price']);
			$this->assign("doPay",$info['doPay']);
			
			$this -> display();
		}else{
			$no = $this->createContractNo();
			$this->assign("bh",$no);
			$this->display();
		}
	}

	/**
	 +----------------------------------------------------------
	 * 新增
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function addContract() {
		//是否已經存在於本地數據庫
		$M = M('zz_contract');
		if ($M->create()) {
			$id = $M->add();
			SysLogs::log("新增合同,id=" . $id);
			$logData["tablename"] = "zz_contract";
			$logData["no"] = $id;
			$logData["createUser"] = $_SESSION['loginName'];
			ZZLogModel::addLog($logData);
			$this -> success('新增成功');
		} else {
			Log::write(M()->getLastSql());
			$this -> error('新增失敗');
		}
	}
	/**
	 +----------------------------------------------------------
	 * 保存编辑的信息
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function saveContract() {
		$M = M('zz_contract');
		if($M->create()){
			$result = $M-> save();
			if (is_int($result)) {
				SysLogs::log("更新合同,id=" . $_POST["id"]);
				$logData["tablename"] = "zz_contract";
				$logData["no"] = $_POST["id"];
				$logData["updateUser"] = $_SESSION['loginName'];
				ZZLogModel::updateLog($logData);
				$this -> success('保存成功');
			} else {
				Log::write(M()->getLastSql());
				$this -> error('保存失敗');
			}
		}else{
			Log::write(M()->getLastSql());
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
	public function removeContract() {
		$id = $_POST["id"];
		if ($id) {
			$data['isShow'] = 0;
			$result = M('zz_contract') ->data($data)-> where("id=" . $id) -> save();
			if(is_int($result)){
				SysLogs::log("删除合同,id=" . $id);
				$this -> success("删除成功");
			}else 
				$this -> error('删除失敗');
		} else {
			$this -> error('无参数');
		}
	}
	/**
     +----------------------------------------------------------
     * 生成編號
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	private function createContractNo()
	{
		//当前年份
		$date = getdate();
		$year = $date["year"];
		$maxNo = M("zz_contract")->max("bh");//获取最近的编号
		if($maxNo && strpos($maxNo,(String)$year)){//若存在且是今年的
			$num = (int)substr($maxNo,12) + 1;
			if(strlen($num) == 1){
				$num = "00" . $num;
			}elseif(strlen($num) == 2){
				$num = "0" . $num;				
			}
			$maxNo = substr($maxNo,0,12) . $num;
		}else{
			$maxNo = "ZZ_CONTRACT_".$year."001";
		}
		return $maxNo;
	}
}
?>