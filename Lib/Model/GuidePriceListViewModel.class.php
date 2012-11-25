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
class GuidePriceListViewModel extends ViewModel {
	/**
     +----------------------------------------------------------
    * admin/guideprice/index 列表显示 
     +----------------------------------------------------------
    * @access public
     +----------------------------------------------------------
    */
	public $viewFields = array(
		'zz_guideprice' => array('id','price', 'month','year'),
		'zz_log' => array('createUser','createTime','updateUser','updateTime','_on' => 'zz_guideprice.id=zz_log.no'),
	);
}
?>