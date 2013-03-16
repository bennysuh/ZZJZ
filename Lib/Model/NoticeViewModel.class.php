<?php
class NoticeViewModel extends ViewModel {
	/**
	 +----------------------------------------------------------
	 * contract組合staff、customer的視圖模型
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public $viewFields = array(
		'zz_notice' => array('id','title','content','isPublish'),
		'zz_log' => array('createUser','createTime','updateUser','updateTime','_on' => 'zz_log.tablename="zz_notice" and zz_notice.id=zz_log.no'),
	);
}
?>