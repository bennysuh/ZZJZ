<?php
class CallLogViewModel extends ViewModel {
	/**
	 +----------------------------------------------------------
	 * contract組合staff、customer的視圖模型
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public $viewFields = array(
		'zz_calllog' => array('id'=>'logID','type','detail','process','callTime'),
		'zz_phone' => array('id'=>'phoneID','name','phone','customerID','_on' => "zz_calllog.phoneID=zz_phone.id"),
		'config_city'=>array('nCid'=>'areaID','sCn'=>'area','_on'=>'zz_phone.areaID=config_city.nCid'),
		'zz_log' => array('createUser','createTime','updateUser','updateTime','_on' =>'zz_log.tablename="zz_calllog" and zz_calllog.id=zz_log.no'),
	);
}
?>