<?php
class FreeTimeViewModel extends ViewModel {
	/**
	 +----------------------------------------------------------
	 * contract組合staff、customer的視圖模型
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public $viewFields = array(
		'zz_freetime' => array('id','staffId'=>'freetime_staffId','startDate','endDate'),
		'zz_staff' => array('staffid', 'ygbh','name', '_on' => 'zz_freetime.staffId=zz_staff.staffid'),
		'zz_log' => array('createUser','createTime','updateUser','updateTime','_on' => 'zz_log.tablename="zz_freetime" and zz_freetime.id=zz_log.no'),
	);
}
?>