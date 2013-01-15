<?php
class ContractViewModel extends ViewModel {
	/**
	 +----------------------------------------------------------
	 * contract組合staff、customer的視圖模型
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public $viewFields = array(
		'zz_contract' => array('id', 'bh', 'customerId', 'staffId', 'startDate', 'endDate','price','doPay','orderDate','updatetime','alternate1','alternate2'),
		'zz_yscustomer' => array('name'=>'customerName','hospital','birthday','expectedDay','address', '_on' => 'zz_contract.customerId=zz_yscustomer.id'),
		'zz_staff' => array('name'=>'staffName','ysLevel', '_on' => 'zz_contract.staffId=zz_staff.staffId'),
		'zz_log' => array('createUser','createTime','updateUser','updateTime','_on' =>'zz_log.tablename="zz_contract" and zz_contract.id=zz_log.no'),
	);
}
?>