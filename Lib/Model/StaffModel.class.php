<?php
class StaffModel extends Model {
	/**
	 +----------------------------------------------------------
	 * Portal获取员工列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getStaffList($firstRow,$listRows) {
		$M = D("StaffView");
		$result = $M->where("isHidden=1")->limit("$firstRow,$listRows")->order("updatetime")->select();
		return $result;
	}
	/**
	 +----------------------------------------------------------
	 *获取员工总数
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getCount()
	{
		return M("zz_staff")->where("isHidden=1")->count();
	}
	public function getStaffInfo()
	{
			$staff = M('zz_staff');
			$staffInfo = $staff -> where("staffid = " . $staffId) -> find();
			$this -> initOptions($staffInfo["jg_province"]);
			$this -> assign('jg_province', $staffInfo['jg_province']);
			$this -> assign('jg_city', $staffInfo["jg_city"]);
			$this -> assign('address', $staffInfo["address"]);
			$this -> assign('remark', $staffInfo["remark"]);
			$this -> assign('ysLevel', $staffInfo["ysLevel"]);
			$this -> assign("name", $staffInfo["name"]);
			$this -> assign("birthday", $staffInfo["birthday"]);
			$this -> assign("whcd", $staffInfo["whcd"]);
			$this -> assign("gzjy", $staffInfo["gzjy"]);
			$this -> assign("ygbh", $staffInfo["ygbh"]);
			// $this -> assign("phone", $staffInfo["phone"]);
			$this -> assign("staffId", $staffId);
			//$this -> assign("images", $staffInfo["images"]);
			//$this -> assign("imgShowIndex", $staffInfo["imgShowIndex"]);
			$this -> assign("lang", $staffInfo["languages"]);

			//查询级别的enum。并转换为数组
			$M = M('zz_stafflevel');
			$levelAttr = $M -> getField("id,level");
			$this -> assign('levelVo', $levelAttr);
			//显示联系方式
			$contact = M("zz_contact");
			$contactArr = $contact -> where("no = " . $staffId . " and tableName = 'zz_staff'") -> order("id") -> select();
			$this -> assign("contactList", $contactArr);
			//显示缩略图
			$data['pid'] = $staffInfo["ygbh"];
			$data['tablename'] = "zz_staff";
			$picList = D("Upload")->getFiles($data);
			$this->assign("picList",$picList);
			$this -> display();
	}
}
?>