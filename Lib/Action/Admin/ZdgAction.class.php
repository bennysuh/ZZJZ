<?php
/**
 +------------------------------------------------------------------------------
 * 钟点工控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class ZdgAction extends EntryAction {
	/**
	 +----------------------------------------------------------
	 * 根据查询条件显示钟点工列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function index() {
		//根据URL参数查询用户
		import("@.ORG.Page");
		//导入分页类
		$keyword = trim($_GET['name']);
		$M = D("ZdgView");
		if($keyword)
			$data["name"] = array("like","%$keyword%");
		if ($_GET['worktime']) {
			$data["worktime"] = array("like","%" . trim($_GET['worktime']) . "%");
		}
		
		if ($_GET['addressRegion']) {
			$data["addressRegion"] = $_GET['addressRegion'];
		}
		
		$count = $M->where($data)->count();
		//分页
		$p = new Page($count, 10);
		$page = $p -> show();
		$list = $M->where($data)->limit($p -> firstRow . " , " . $p -> listRows)->order('updateTime desc')->select();
		$addressRegionList = D("Region")->getSubsByParentID(440100);
		$this->assign("addressRegionList",$addressRegionList);	
		$this -> assign('page', $page);
		$this -> assign('list', $list);
		$this -> display();
	}

	/**
	 +----------------------------------------------------------
	 * 新建钟点工
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function addZdg() {
		//是否已經存在於本地數據庫
		$Zdg = M('zz_zdg');
		$data = $Zdg -> create();
		//获取POST数据
		$count = $Zdg -> where("name = '" . $_POST["name"] . "'") -> count();
		if ($count) {
			$this -> error("钟点工名已存在");
		}

		$key = $Zdg -> data($data) -> add();
		
		if ($key) {
			//保存图片到zz_upload
			if($data["images"]){
				$imgArr = explode(",", $data['images']);
			}
			SysLogs::log("新增钟点工" . $_POST["name"]);
			$logData["tablename"] = "zz_zdg";
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
	 * 新增联系方式
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function addContact() {
		$json = $_POST["json"];
		$json = str_replace("\\","",$json);//apache低版本不会自动处理引号
		$json=json_decode($json);
		$arr = objectToArray($json->data);//若json带引号则无法取到
		if (count($arr) == 0) {
			$this -> error('无联系方式');
		} else {
			foreach ($arr as $value) {
				$contact = M("zz_contact");
				$contact -> no = $json -> no;
				$contact -> tableName = "zz_zdg";
				$v = arrayToObject($value);
				$contact -> type = $v -> type;
				$contact -> fieldA = $v -> content;
				$key = $contact -> add(); 
				if (!$key) {
					$this -> error('联系方式新增失敗');
				}else{
					SysLogs::log("新增联系方式" . $key);
				}
			}
			$this -> success("新增成功");
		}
	}

	/**
	 +----------------------------------------------------------
	 * 显示要编辑钟点工信息
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function editZdg() {
		$id = $_GET["id"];
		//编辑页面
		if ($id) {
			$zdgInfo = M('zz_zdg') -> where("id = " . $id) -> find();
			$this -> initOptions(true);
			$this -> assign('provinceID', $zdgInfo['jg_province']);
			$cityList = D("Region")->getSubsByParentID($zdgInfo['jg_province']);
			$this->assign("cityList",$cityList);
			$qxList = D("Region")->getSubsByParentID($zdgInfo['jg_city']);
			$this->assign("qxList",$qxList);
			$this -> assign('cityID', $zdgInfo["jg_city"]);
			$this -> assign('qxID', $zdgInfo["jg_qx"]);
			$this -> assign('address', $zdgInfo["address"]);
			$this -> assign('remark', $zdgInfo["remark"]);
			$this -> assign("name", $zdgInfo["name"]);
			$this -> assign("gzjy", $zdgInfo["gzjy"]);
			$this -> assign("worktime", $zdgInfo["worktime"]);
			$this -> assign("birthday", $zdgInfo["birthday"]);
			$this -> assign("addressRegion", $zdgInfo["addressRegion"]);
			$this -> assign("ygbh", $zdgInfo["ygbh"]);
			$this -> assign("id", $id);
			$this->assign("imagePath",$zdgInfo['imagePath']);
			//显示联系方式
			$contact = M("zz_contact");
			$contactArr = $contact -> where("no = " . $id . " and tableName = 'zz_zdg'") -> order("id") -> select();
			$this -> assign("contactList", $contactArr);
			
			$this -> display();
		} else {
			$this -> initOptions();
			$no = $this->createYgbh();
			$this->assign("ygbh",$no);
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
	private function initOptions($status=false) {
		if(!$status){
			$this->assign("provinceID",440000);
			//默认广州
			$cityList = D("Region")->getSubsByParentID(440000);
			$this->assign("cityList",$cityList);	
			$this->assign("cityID",440100);
			$qxList = D("Region")->getSubsByParentID(440100);
			$this->assign("qxList",$qxList);		
		}
		$provinceList = D("Region")->getProvinces();
		$this->assign("provinceList",$provinceList);	
		$addressRegionList = D("Region")->getSubsByParentID(440100);
		$this->assign("addressRegionList",$addressRegionList);	
		$this->assign("addressRegionID",440101);	
		
	}
	
    /**
	 +----------------------------------------------------------
	 * 根据省份查询城市
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getCityByProv()
	{
		if($_POST['pid']){//页面切换省份
			$cities = D('City') -> getCityByProv($_POST['pid']);
			$this->success($cities);
		}
	}
	
	/**
	 +----------------------------------------------------------
	 * 保存编辑的钟点工信息
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function saveZdg() {
		//是否已經存在於本地數據庫
		$zdg = M('zz_zdg');
		if ($_POST['name']) {//保存编辑信息
			$data = $zdg->create();
			
			$result = $zdg -> save($data);
			
			SysLogs::log("更新钟点工" . $POST["name"] );
			$logData["tablename"] = "zz_zdg";
			$logData["no"] = $_POST['id'];
			$logData["operate"] = "update";
			$logData["updateUser"] = $_SESSION['loginName'];
			ZZLogModel::updateLog($logData);
		}
		//保存成功返回影响的记录数不成功返回false。有可能返回0
		if (is_int($result)) {
			$this -> success('保存成功');
		} else {
			$this -> error('保存失敗');
		}
	}
	
	
	//更新联系方式
	public function saveContact() {
		$json = $_POST["json"];
		$json = str_replace("\\","",$json);
		$json=json_decode($json);
		$arr = objectToArray($json->data);//若json带引号则无法取到
		if (count($arr) == 0) {
			$this -> error('无联系方式');
		} else {
			$contact = M("zz_contact");
			$contact -> where('no= ' . $json -> no) -> delete();
			SysLogs::log("删除联系方式,no=" . $json->no);
			//删除已有记录
			//新增编辑的记录
			foreach ($arr as $value) {
				$v = arrayToObject($value);
				$data['no'] = $json -> no;
				$data['type'] = $v -> type;
				$data['fieldA'] = $v -> content;
				$data['tableName'] = "zz_zdg";
				if (!($contact -> add($data))) {
					$this -> error('联系方式保存失敗');
				}
			}
			SysLogs::log("新增联系方式,no=" . $json->no);
			$this -> success("保存成功");
		}
	}

	/**
	 +----------------------------------------------------------
	 * 刪除钟点工
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function removeZdg() {
		$id = $_POST["id"];
		if ($id) {
			$data = M('zz_zdg') ->field('imagePath')-> where("id=" . $id) -> find();
			M('zz_zdg') -> where("id=" . $id) -> delete();
			SysLogs::log("删除钟点工,id=" . $id);
			$result = $this -> delContact($id);
			if (is_int($result)) {
				if ($data['imagePath']) {
					$result = unlink("Public/Uploads/zdg/". $data['imagePath']);
					if (!$result) {
						$this->error("删除图片失败");
					}
				}
				
				$this -> success('刪除成功');
			} else {
				$this -> error('联系方式刪除失败');
			}
		} else {
			$this -> error('无参数');
		}
	}

	/**
	 +----------------------------------------------------------
	 * 刪除联系方式
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function delContact($no) {
		$result = M('zz_contact') -> where("no=" . $no . " and tableName = 'zz_zdg'") -> delete();
		SysLogs::log("删除联系方式,no=" . no);
		return $result;
	}

	
	
	
	/**
     +----------------------------------------------------------
     * 生成編號
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	private function createYgbh()
	{
		//当前年份
		$date = getdate();
		$maxNo = M("zz_zdg")->max("ygbh");//获取最近的编号
		if($maxNo){//若存在
			$num = (int)substr($maxNo,7) + 1;
			if(strlen($num) == 1){
				$num = "00" . $num;
			}elseif(strlen($num) == 2){
				$num = "0" . $num;				
			}
			$maxNo = substr($maxNo,0,7) . $num;
		}else{
			$maxNo = "ZZ_ZDG_".$year."001";
		}
		return $maxNo;
	}
}
?>