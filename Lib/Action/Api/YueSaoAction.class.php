<?php
/**
 +------------------------------------------------------------------------------
 * 员工控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class YueSaoAction extends Action {
	/********************** list action ****************************************/
	/**
	 +----------------------------------------------------------
	 * 列表 取前20個
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function yuesaoList() {
		
		$start = $_REQUEST['from'] ? $_REQUEST['from'] : 0;
		$num = $_REQUEST['num'] ? $_REQUEST['num'] : 20;
		if ($_REQUEST["province"]) {
			$where["jg_province"] = array("like", '%' . $_REQUEST["province"] . '%'); 
		}
		if ($_REQUEST["level"]) {
			$where["ysLevel"] = array("like", '%' . $_REQUEST["level"] . '%'); 
		}
		
		if ($where) {
			$where['_logic'] = 'and';
			$cond['_complex'] = $where;		
		}
		$cond = $cond ? $cond : '';
		$yuesaoList = D("Staff")->getYueSaoList_Api($start, $num, $cond);
		
		echo $_GET['jsoncallback'] . "(". json_encode($yuesaoList) . ")";
	}
	
	private function callbackError($jsonCallback)
	{
		$response['info'] = 'Get Data Failed';
		$response['status'] = 0;
		echo $jsonCallback . "(". json_encode($response) . ")";
	}
	
	public function optionList()
	{
		$levelList = M("zz_stafflevel")->field("id, level")->order("id desc")->select();
		$provinceList = M("provincial")->where("pid in (select distinct `jg_province` from zz_staff where `isHidden` = 1)")->order("pid asc")->select();
		$option['level'] = $levelList;
		$option['province'] = $provinceList; 
		if ($levelList && $provinceList) {
			echo $_GET['jsoncallback'] . "(". json_encode($option) . ")";
		} else {
			$this->callbackError($_GET['jsoncallback']);
		}
	}
	
	/********************** info action ****************************************/
	/**
	 +----------------------------------------------------------
	 * 根据ID返回指定ID及前后十条记录
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getInfoListById() {
		$id = $_REQUEST["id"];
		if ($id && $id != "undefined") {
			$info = D("Staff") -> getInfoListByID_Api($id);
			if ($info) {
				echo $_GET['jsoncallback'] . "(". json_encode($info) . ")";
			} else {
				$this->callbackError($_GET['jsoncallback']);
			}
		}else{
			$this->callbackError($_GET['jsoncallback']);
		}
	}
	
	/**
	 +----------------------------------------------------------
	 * 根据ID返回指定info
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getInfo() {
		$id = $_REQUEST["id"];
		if ($id && $id != "undefined") {
			$info = D("Staff") -> getInfoByID_Api($id);
			if ($info) {
				echo $_GET['jsoncallback'] . "(". json_encode($info) . ")";
			} else {
				$this->callbackError($_GET['jsoncallback']);
			}
		}else{
			$this->callbackError($_GET['jsoncallback']);
		}
	}
	
	/**
	 +----------------------------------------------------------
	 * 根据ID返回指定ID前十条记录
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getFrontListById() {
		$id = $_REQUEST["id"];
		if ($id && $id != "undefined") {
			$list = D("Staff") -> getFrontListByID_Api($id);
			if ($list) {
				echo $_GET['jsoncallback'] . "(". json_encode($list) . ")";
			} else {
				$this->callbackError($_GET['jsoncallback']);
			}
		}else{
			$this->callbackError($_GET['jsoncallback']);
		}
	}
	
	/**
	 +----------------------------------------------------------
	 * 根据ID返回指定ID后十条记录
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getBackListById() {
		$id = $_REQUEST["id"];
		if ($id && $id != "undefined") {
			$list = D("Staff") -> getBackListByID_Api($id, FALSE);
			if ($list) {
				echo $_GET['jsoncallback'] . "(". json_encode($list) . ")";
			} else {
				$this->callbackError($_GET['jsoncallback']);
			}
		}else{
			$this->callbackError($_GET['jsoncallback']);
		}
	}
	
	
	/**
	 +----------------------------------------------------------
	 * 选择查询列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function searchStaff()
	{
		//根据URL参数查询用户
		import("@.ORG.Page");
		//导入分页类
		$M = M("zz_staff");
		//获取查询参数
		if($_GET['name']){
			$name = $_GET['name'];
			$data['name'] = array('like',"%$name%");
		}
		$count = $M->where($data)->count();
		$p = new Page($count, 10);
		$page = $p -> show();
		$list = $M->where($data)->limit($p -> firstRow.','.$p -> listRows)->order("updatetime desc")->select();

		$this -> assign('list', $list);
		$this -> display();
	}
	
	public function about()
	{
		$info = M("zz_configpage") ->where("page='appAbout'")-> find();

		if ($info) {
			echo $_GET['jsoncallback'] . "(". json_encode($info) . ")";
		} else {
			$this->callbackError($_GET['jsoncallback']);
		}
	}

}
?>