<?php
class CustomerModel extends Model {
	/**
	 +----------------------------------------------------------
	 *获取customer列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getCustomerlist()
	{
		$result = M("zz_yscustomer")->field("id,name")->order("name asc")->select();
		return $result;
	}		
}
	