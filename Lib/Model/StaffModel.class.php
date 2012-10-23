<?php
class StaffModel extends Model {
	/**
	 +----------------------------------------------------------
	 * Portal获取推荐员工列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getRecommendStaffList($firstRow,$listRows) {
		$M = D("StaffView");
		$result = $M->where("isHidden=1 and ysLevel >= 4")->limit("$firstRow,$listRows")->order("updatetime")->select();
		return $result;
	}
	public function getLastStaffList($firstRow,$listRows) {
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
	
	public function getStaffInfo($staffId)
	{
			$staffInfo = D('StaffView') -> where("staffid = " . $staffId) -> find();
			return $staffInfo;
	}
}
?>