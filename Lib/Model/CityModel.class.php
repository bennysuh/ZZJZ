<?php
class CityModel extends Model {
	/**
	 +----------------------------------------------------------
	 * 获取城市
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getCityByProv($pid) {
		if ($pid) {
			$M = M("city");
			$result = $M -> where('pid=' . $pid) ->order("cid asc") -> getField("cid,city");
			return $result;
		}else{
			return false;
		}
	}

}
?>