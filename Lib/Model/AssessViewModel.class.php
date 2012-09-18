<?php
class AssessViewModel extends ViewModel {
	/**
	 +----------------------------------------------------------
	 * assess組合staff、customer、contract的視圖模型
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public $viewFields = array(
		'zz_assess' => array('id'=>'id', 'staffId', 'orderId', 'fwtdLevel', 'zyjnLevel','yzczlLevel','wsxgLevel','assess','updatetime'),
		'zz_contract' => array('bh','startDate','endDate', '_on' => 'zz_assess.orderId=zz_contract.id'),
		'zz_yscustomer' => array('name'=>'customerName','_on' => 'zz_contract.customerId=zz_yscustomer.id'),
		'zz_staff' => array('name'=>'staffName', '_on' => 'zz_assess.staffId=zz_staff.staffId')
	);
}
?>