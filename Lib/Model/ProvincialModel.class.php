<?php
class ProvincialModel extends Model {
	/**
     +----------------------------------------------------------
    * 通过省份名获取省份
     +----------------------------------------------------------
    * @access public
     +----------------------------------------------------------
    */
	public function getPidByProv($prov) {
		$result = M('provincial') -> field('pid') -> where("provincial like '%" . $prov . "%'") -> find();
		return $result;
	}
	/**
     +----------------------------------------------------------
    * 获取省份列表
     +----------------------------------------------------------
    * @access public
     +----------------------------------------------------------
    */
	public function getProvsOrderById($pid)
	{
		$M = M("provincial");
		if($pid){
			//将指定pid排第一个
			$result = $M->order("(case when pid='" . $pid . "' then 1 ELSE 4 END),pid asc") ->select();
			return $result;
		}
		return null;
	}
	/**
     +----------------------------------------------------------
    * 获取省份列表
     +----------------------------------------------------------
    * @access public
     +----------------------------------------------------------
    */
	public function getProvinces()
	{
		$M = M("provincial");
		//默认将广东排在第一个
		//$result = $M->order("(case when pid=20 then 1 ELSE 4 END),pid asc") ->select();
		$result = $M->order("pid asc") ->getField("pid,provincial");
		return $result;
	}
	
}
?>