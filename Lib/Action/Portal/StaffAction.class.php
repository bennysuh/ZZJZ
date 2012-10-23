<?php
 /**
 +------------------------------------------------------------------------------
 * STAFF控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class StaffAction extends Action {
	/**
	 +----------------------------------------------------------
	 * 根据查询条件显示员工列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function index() {
		//根据URL参数查询用户
		import("@.ORG.Page");
		//导入分页类
		$keyword = $_GET['name'];
		$M = D("StaffView");
		
		$count = count($M->count());
		//分页
		$p = new Page($count, 10);
		$page = $p -> show();
		$list = $M ->limit($p -> firstRow.','.$p -> listRows)->select();
		$this -> assign('page', $page);
		$this -> assign('list', $list);
		$this -> display();
	}
	/**
	 +----------------------------------------------------------
	 * 员工详细资料
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function viewStaffDetail()
	{
		$staffId = $_GET["staffId"];
		$staff = D("Staff")->getStaffInfo($staffId);
		$this -> assign('jg_province', $staffInfo['jg_province']);
		$this -> assign('jg_city', $staffInfo["jg_city"]);
		$this -> assign('address', $staffInfo["address"]);
		$this -> assign('remark', $staffInfo["remark"]);
		$this -> assign('ysLevel', $staffInfo["ysLevel"]);
		$this -> assign("name", $staffInfo["name"]);
		$this -> assign("age", parseBirthdayToAge($staffInfo["birthday"]));
		$this -> assign("whcd", $staffInfo["whcd"]);
		$this -> assign("gzjy", $staffInfo["gzjy"]);
		$this -> assign("ygbh", $staffInfo["ygbh"]);
		$this -> assign("lang", $staffInfo["languages"]);
		$this->display();
	}
}
?>