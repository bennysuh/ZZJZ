<?php
class ZZLogModel extends Model {
	/**
	 +----------------------------------------------------------
	 * 新增记录
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public static function addLog($data) {
		if ($data) {
			$M = M("zz_log");
			$data["createTime"] = date('Y-m-d H:i:s');
			$data["updateUser"] = $_SESSION['loginName'];
			$data["createUser"] = $_SESSION['loginName'];
			$data["updateTime"] = $data["createTime"];
			$key = $M ->data($data)->add();
			if($key)
				return true;
			else 
				return false;
		}else{
			return false;
		}
	}
	/**
	 +----------------------------------------------------------
	 * 更新记录
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public static function updateLog($data)
	{
		if ($data) {
			$M = M("zz_log");
			$logData["updateUser"] = $data['updateUser'];
			$logData["updateTime"] = date('Y-m-d H:i:s');
			$key = $M ->where("no=" . $data['no'] . " and tablename='" . $data['tablename'] . "'")->data($logData)->save();
			if($key)
				return true;
			else 
				return false;
		}else{
			return false;
		}
	}
}
?>