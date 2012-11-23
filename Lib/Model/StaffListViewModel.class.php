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
class StaffListViewModel extends ViewModel {
	/**
     +----------------------------------------------------------
    * admin/staff/index 列表显示 
     +----------------------------------------------------------
    * @access public
     +----------------------------------------------------------
    */
	public $viewFields = array(
		'zz_staff' => array('staffid','ygbh', 'name', 'jg_city','birthday','isHidden'),
		'city' => array('city','_on' => 'zz_staff.jg_city=city.cid and zz_staff.jg_province=city.pid'),
		'zz_log' => array('createUser','createTime','updateUser','updateTime','_on' => 'zz_staff.staffid=zz_log.no'),
	);
}
?>