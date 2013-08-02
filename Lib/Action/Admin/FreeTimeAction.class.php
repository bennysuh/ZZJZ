<?php
/**
 +------------------------------------------------------------------------------
 * 空檔期控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class FreeTimeAction extends EntryAction {
	/**
	 +----------------------------------------------------------
	 * 根据查询条件显示列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function index() {
		if($_GET['name'])
		{
			$name = $_GET['name'];
			$data["name"] =  array('like',"%$name%");
		}
		if($_GET['freeDate'])
		{
			$data["startDate"] = array("ELT", $_GET["freeDate"]);
			$data["endDate"] = array("EGT", $_GET["freeDate"]);
		}
		$M = M("zz_freetime");
		//月嫂下拉列表
		$staffList = D("Staff")->getStafflist();
		import("@.ORG.Page");
		$count = D('FreeTimeView')->where($data)->count();
		$p = new Page($count, 10);
		$page = $p -> show();
		$result = D('FreeTimeView')->where($data)->limit($p -> firstRow.','.$p -> listRows)->order("updatetime desc")->select();
		foreach ($result as $key=>$freetime) {
			if($freetime['endDate']){
				if(strtotime($freetime['endDate']) < strtotime(date("Y-m-d"))){
					$result[$key]["status"] = "非空档";
				}else{
					$result[$key]["status"] = "空档";
				}
			}else{
				$result[$key]["status"] = "空档期无截至日期";
			}
		}
		
		$this -> assign('page', $page);
		$this->assign("list",$result);
		
		$this->assign("staffList",json_encode($staffList));
		$this->display();
	}

	/**
	 +----------------------------------------------------------
	 * 新增
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function addFreetime() {
		//是否已經存在於本地數據庫
		$M = M('zz_freetime');
		$data = $M->create();
		$key = $M ->data($data)-> add();//获取新增返回的id值用于添加到联系方式表中
		if ($key) {
			SysLogs::log("新增空檔期,id=" . $key);
			$logData["tablename"] = "zz_freetime";
			$logData["no"] = $key;
			ZZLogModel::addLog($logData);
			$this -> success("新增成功");
		} else {
			$this -> error('增加失敗');
		}
	}
	/**
	 +----------------------------------------------------------
	 * 显示新增/编辑信息
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function editFreetime()
	{
		$freetimeId = $_GET['id'];//已有记录
		if($freetimeId){//assess列表过来的
			$M = D('AssessView');
			$result = $M->where("zz_assess.id=" . $assessId)->find();
			if($result){
				$this->assign("ygbh",$result['ygbh']);
				$this->assign("staffName",$result['staffName']);
				$this->assign("startDate",$result['startDate']);
				$this->assign("endDate",$result['endDate']);
				$this->display();
			}else{
				$this->error("无此空檔期");
			}
		}else{
			$this->error("缺少订单号");
		}
	}
	/**
	 +----------------------------------------------------------
	 * 更新
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function saveFreetime(){
		$M = M('zz_freetime');
		$data = $M->create();
		if($data){
			$M ->data($data)->save();
			SysLogs::log("更新空檔期,id=" . $data["id"]);
			$logData["tablename"] = "zz_freetime";
			$logData["no"] = $data["id"];
			$logData["operate"] = "update";
			$logData["updateUser"] = $_SESSION['loginName'];
			ZZLogModel::updateLog($logData);
			$this->success("更新成功");
		}else{
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
	public function delFreeTime() {
		$id = $_POST["id"];
		if ($id) {
			$result = M('zz_freetime') -> where("id=" . $id) -> delete();
			SysLogs::log("删除空檔期,id=" . $id);
			$logData["tablename"] = "zz_freetime";
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
}
?>