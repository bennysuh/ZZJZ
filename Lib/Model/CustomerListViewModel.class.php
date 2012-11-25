<?php
// +----------------------------------------------------------------------
// | ZZJZ [ ENJOY LIFE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2012 http://www.gzzzjz.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: david <mchuang1104@gmail.com>
// +----------------------------------------------------------------------
// $Id$
class CustomerListViewModel extends ViewModel {
	/**
     +----------------------------------------------------------
    * admin/customer/index 列表显示 
     +----------------------------------------------------------
    * @access public
     +----------------------------------------------------------
    */
	public $viewFields = array(
		'zz_yscustomer' => array('id','name', 'expectedDay'),
		'zz_log' => array('createUser','createTime','updateUser','updateTime','_on' => 'zz_log.tablename="zz_yscustomer" and zz_yscustomer.id=zz_log.no'),
	);
}
?>