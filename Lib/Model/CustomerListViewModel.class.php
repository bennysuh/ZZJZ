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
		'zz_customer' => array('id','name','customerType'),
		'zz_log' => array('createUser','createTime','updateUser','updateTime','_on' => 'zz_log.tablename="zz_customer" and zz_customer.id=zz_log.no'),
	);
}
?>