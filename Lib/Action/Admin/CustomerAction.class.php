<?php
/**
 +------------------------------------------------------------------------------
 * 客户控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class CustomerAction extends EntryAction {
	/**
	 +----------------------------------------------------------
	 * 根据查询条件显示列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function index() {
		$this -> showList();
	}

	/**
	 +----------------------------------------------------------
	 * 新增客户
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function addCustomer() {
		//是否已經存在於本地數據庫
		$M = M('zz_customer');
		$lang = $_POST['lang'];
		//$M -> create();:checkbox的post参数。不会将该参数加入到数据库，因此手动设置data数组
		$data = $M -> create();
		$data['lang'] = join(",", $lang);
		//获取POST数据
		$count = $M -> where("name = '" . $_POST["name"] . "'") -> count();
		if ($count) {
			$this -> error("该名称已存在");
		}
		$key = $M -> data($data) -> add();
		//获取新增返回的id值用于添加到联系方式表中
		if ($key) {
			SysLogs::log("新增客户,id=" . $key);
			$logData["tablename"] = "zz_customer";
			$logData["no"] = $key;
			$logData["createUser"] = $_SESSION['loginName'];
			ZZLogModel::addLog($logData);
			$this -> success($key);
		} else {
			$this -> error('增加失敗');
		}
	}

	/**
	 +----------------------------------------------------------
	 * 更新联系方式
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function saveContact() {
		$json = $_POST["json"];
		$json = str_replace("\\", "", $json);
		$json = json_decode($json);
		$arr = objectToArray($json -> data);
		if (count($arr) == 0) {
			$this -> error("缺少联系方式");
		}
		$result = D("Contact") -> saveContact($json -> no, $arr, "zz_Customer");
		if ($result)
			$this -> success("保存成功");
		else
			$this -> error('保存联系方式失敗');
	}

	/**
	 +----------------------------------------------------------
	 * 显示编辑信息
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function editCustomer() {
		$id = $_GET["id"];
		//编辑页面
		if ($id) {
			$M = M('zz_customer');
			$info = $M -> where("id = " . $id) -> find();
			$this -> assign("name", $info["name"]);
			$this->assign("sex",$info['sex']);
			$this -> assign("id", $info['id']);
			$this -> assign("remark", $info['remark']);
			//显示联系方式
			$contactArr = D("Contact") -> getContact($id, "zz_customer");
			$this -> assign("contactList", $contactArr);

			$this -> display();
			//不要执行2次display。
		} else {
			$this -> display();
		}
	}

	/**
	 +----------------------------------------------------------
	 * 初始化下拉选项
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	private function initOptions() {
		//级别
		$M = M('zz_stafflevel');
		$levelAttr = $M -> getField("id,level");
		$this -> assign('ysLevelList', $levelAttr);

		//语言
		$M = M('zz_languages');
		$langAttr = $M -> getField("itemid,itemname");
		$this -> assign('langList', $langAttr);
	}

	/**
	 +----------------------------------------------------------
	 * 保存编辑的客户信息
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function saveCustomer() {
		//是否已經存在於本地數據庫
		$M = M('zz_customer');
		$data = $M -> create();
		if ($M -> create()) {
			$result = $M -> where('name="' . $data['name'] . '"') -> select();
			foreach ($result as $key => $value) {
				if ($value['id'] != $data['id']) {
					$contacts = M('zz_contact') -> where('tableName="zz_customer" and no ="' . $value['id'] . '"') -> select();

					foreach ($contacts as $k => $v) {
						foreach ($_POST['contactType'] as $j => $type) {
							if ($v['fieldA'] == $_POST['contactValue'][$j] && $v['type'] == $type) {
								$this -> error('已有该用户名和联系方式一样的客户！');
							}
						}
					}
				}
			}
			
			$result = $M -> data($data) -> save();
			//保存成功返回影响的记录数不成功返回false。若无更改则返回0
			if (is_int($result)) {
				SysLogs::log("保存客户信息,id=" . $data["id"]);
				$logData["tablename"] = "zz_customer";
				$logData["no"] = $data["id"];
				$logData["updateUser"] = $_SESSION['loginName'];
				ZZLogModel::updateLog($logData);
				$this -> success('保存成功');
			} else {
				$this -> error('保存失敗');
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
	public function removeCustomer() {
		$id = $_POST["id"];
		if ($id) {
			M('zz_customer') -> where("id=" . $id) -> delete();
			$result = D("Contact") -> delContact($id);
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
	 * 列表显示
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	private function showList() {
		//根据URL参数查询用户
		import("@.ORG.Page");
		//导入分页类
		$M = D("CustomerListView");
		//获取查询参数
		if ($_GET['name']) {
			$name = $_GET['name'];
			$data['name'] = array('like', "%$name%");
		}

		$count = $M -> where($data) -> count();
		$p = new Page($count, 10);
		$page = $p -> show();
		$list = $M -> where($data) -> limit($p -> firstRow . ',' . $p -> listRows) -> order("updatetime desc") -> select();
		foreach ($list as $key => $value) {
			$types = $value['customerType'];
			$typeArr = explode(',', $types);
			if (count($typeArr) == 1) {
				if ($types == 'ysCustomer') {
					$list[$key]['customerType'] = '月嫂客户';
				} elseif ($types == 'zdgCustomer') {
					$list[$key]['customerType'] = '钟点工客户';
				}
			} else {
				$types = '';
				foreach ($typeArr as $k => $v) {
					if ($v == 'ysCustomer') {
						$types .= '月嫂客户,';
					} elseif ($v == 'zdgCustomer') {
						$types .= '钟点工客户,';
					}
				}
				$list[$key]['customerType'] = substr($types, 0, -1);
			}
		}
		$this -> assign('page', $page);
		$this -> assign('list', $list);
		$this -> display();
	}

	/**
	 +----------------------------------------------------------
	 * 选择客户查询列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function searchCustomer() {
		$this -> showList();
	}

	/**
	 +----------------------------------------------------------
	 * 根据ID返回指定ID记录
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getCustomerById() {
		$id = $_POST["id"];
		//编辑页面
		if ($id) {
			$M = M('zz_customer');
			$info = $M -> where("id = " . $id) -> order("name desc") -> find();
			$this -> success(json_encode($info));
		} else {
			$this -> error("查询失败");
		}
	}

	/**
	 +----------------------------------------------------------
	 * 编辑客户记录
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function editCustomerLog() {
		$id = $_GET["id"];
		$this -> initOptions();
		//编辑页面
		if ($id) {
			$M = M('zz_yscustomerlog');
			$info = $M -> where("id = " . $id) -> find();
			$this -> assign('address', $info["address"]);
			$this -> assign('ysRemark', $info["ysRemark"]);
			$this -> assign('ysLevel', $info["ysLevel"]);
			$this -> assign("name", $info["name"]);
			$this -> assign("id", $info['id']);
			$this -> assign("area", $info['area']);
			$this -> assign("hospital", $info['hospital']);
			//checkbox多选框。传递的是数组 若传给checkbox必须是lang[].checkbox的checked="lang[]" .
			//checkbox存在缓存问题。
			//$this -> assign("lang[]", explode(",", $info['lang']));
			$this -> assign("lang", $info['lang']);
			$this -> assign("py", $info['py']);
			$this -> assign("birthday", $info['birthday']);
			$this -> assign("expectedDay", $info['expectedDay']);
			//显示联系方式
			$contactArr = D("Contact") -> getContact($id, "zz_customer");
			$this -> assign("contactList", $contactArr);
			$this -> display();
		} else {
			$this -> display();
		}
	}

	/**
	 +----------------------------------------------------------
	 * 删除客户记录
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function removeCustomerLog() {
		$logID = $_POST["logID"];
		if ($logID && $_POST['logType']) {
			if($_POST['logType'] == "月嫂记录"){
				$result = M("zz_yscustomerlog")->where("id=".$_POST['logID'])->delete();
			}else{
				$result = M("zz_zdgcustomerlog")->where("id=".$_POST['logID'])->delete();
			}
			if ($result) {
				$this->success("删除成功");
			} else {
				$this->error("删除失败");
			}
		} else {
			$this -> error("no customer log id");
		}
	}

	public function editYsLog() {
		if ($_GET['customerID']) {
			$customerID = $_GET["customerID"];
			
			
			$customer = M("zz_customer")->where("id=$customerID")->find();
			$this -> initOptions();
			$this->assign("customerName",$customer['name']);
			$this->assign("customerID",$customerID);
			
			$this -> assign("id", $info['id']);
			//显示联系方式
			$contactArr = D("Contact") -> getContact($customerID, "zz_customer");
			
			$this -> assign("contactList", $contactArr);
			
			if ($_GET["logID"]) {
				$logID = $_GET["logID"];
				$M = M('zz_yscustomerlog');
				$info = $M -> where("id = " . $logID) -> find();
				$this -> assign('address', $info["address"]);
				$this -> assign('ysRemark', $info["ysRemark"]);
				$this -> assign('ysLevel', $info["ysLevel"]);
				$this -> assign("name", $info["name"]);
				$this -> assign("id", $info['id']);
				$this -> assign("area", $info['area']);
				$this -> assign("hospital", $info['hospital']);
				//checkbox多选框。传递的是数组 若传给checkbox必须是lang[].checkbox的checked="lang[]" .
				//checkbox存在缓存问题。
				//$this -> assign("lang[]", explode(",", $info['lang']));
				$this -> assign("lang", $info['lang']);
				$this -> assign("py", $info['py']);
				$this -> assign("birthday", $info['birthday']);
				$this -> assign("expectedDay", $info['expectedDay']);
			} 
			
			$this -> display();
		} else {
			$this -> error("缺少ID");
		}
	}
	
	public function saveYsLog()
	{
		//是否已經存在於本地數據庫
		$M = M('zz_yscustomerlog');
		$data = $M -> create();
		if ($M -> create()) {
			$data['lang'] = join(",", $_POST['lang']);
			if($_POST['id']){
				$result = $M -> data($data) -> save();
				if (is_int($result)) {
					SysLogs::log("保存客户月嫂记录信息,id=" . $_POST["id"]);
					$logData["tablename"] = "zz_yscustomerlog";
					$logData["no"] = $_POST["id"];
					$logData["updateUser"] = $_SESSION['loginName'];
					ZZLogModel::updateLog($logData);
					$this->success("保存成功");
				} else {
					$this -> error('保存失敗');
				}
			}else{
				$result = $M -> add($data);
				if ($result) {
					SysLogs::log("新增客户月嫂记录信息,id=" . $_POST["id"]);
					$logData["tablename"] = "zz_yscustomerlog";
					$logData["no"] = $result;
					ZZLogModel::addLog($logData);
					$this->success("新增成功");
				} else {
					$this -> error('新增失敗');
				}
			}
		} else {
			$this -> error('缺少参数');
		}
	}
	
	public function customerLog()
	{
		if ($_GET['customerID']) {
			$customer = M("zz_customer")->where("id=".$_GET['customerID'])->find();
			
			$this->assign("customerName",$customer['name']);
			$ysLogs = D('YsLogView')->where('customerID=' . $_GET['customerID'])->order("updateTime desc")->select();
			
			foreach ($ysLogs as $key => $value) {
				$ysLogs[$key]['logType'] = '月嫂记录';
			}
			$zdgLogs = D("ZdgLogView")->where('customerID=' . $_GET['customerID'])->order("updateTime desc")->select();
			foreach ($zdgLogs as $key => $value) {
				$zdgLogs[$key]['logType'] = '钟点工记录';
			}
			if ($ysLogs) {
				if ($zdgLogs) {
					$logs = array_merge($ysLogs,$zdgLogs);
				} else {
					$logs = $ysLogs;
				}
			} else {
				$logs = $zdgLogs;
			}
			//根据更新时间降序排序
			$logs = multi_array_sort($logs,"updateTime",SORT_DESC);
			$this->assign("list",$logs);
			$this->assign("customerID",$_GET['customerID']);
			$this->display();	
		} else {
			$this->error("no customer id");
		}
	}
	
	public function editZdgLog() {
		if ($_GET['customerID']) {
			$customerID = $_GET["customerID"];
			
			$customer = M("zz_customer")->where("id=$customerID")->find();
			$this->assign("customerName",$customer['name']);
			$this->assign("customerID",$customerID);
			
			$this -> assign("id", $info['id']);
			//显示联系方式
			$contactArr = D("Contact") -> getContact($customerID, "zz_customer");
			
			$this -> assign("contactList", $contactArr);
			
			if ($_GET["logID"]) {
				$logID = $_GET["logID"];
				$this->assign("id",$logID);
				$M = M('zz_zdgcustomerlog');
				$info = $M -> where("id = " . $logID) -> find();
				$this -> assign('address', $info["address"]);
				$this -> assign('remark', $info["remark"]);
				$this -> assign('personNumber', $info["personNumber"]);
				$this -> assign("area", $info["area"]);
				$this -> assign("habits", $info['habits']);
			} 
			
			$this -> display();
		} else {
			$this -> error("缺少ID");
		}
	}
	
	public function saveZdgLog()
	{
		//是否已經存在於本地數據庫
		$M = M('zz_zdgcustomerlog');
		$data = $M -> create();
		if ($M -> create()) {
			if($_POST['id']){
				$result = $M -> data($data) -> save();
				if (is_int($result)) {
					SysLogs::log("保存客户钟点工记录信息,id=" . $_POST["id"]);
					$logData["tablename"] = "zz_zdgcustomerlog";
					$logData["no"] = $_POST["id"];
					$logData["updateUser"] = $_SESSION['loginName'];
					ZZLogModel::updateLog($logData);
					$this->success("保存成功");
				} else {
					$this -> error('保存失敗');
				}
			}else{
				$result = $M -> add($data);
				if ($result) {
					SysLogs::log("新增客户钟点工记录信息,id=" . $data["id"]);
					$logData["tablename"] = "zz_zdgcustomerlog";
					$logData["no"] = $result;
					ZZLogModel::addLog($logData);
					$this->success("新增成功");
				} else {
					$this -> error('新增失敗');
				}
			}
		} else {
			$this -> error('缺少参数');
		}
	}

}
?>