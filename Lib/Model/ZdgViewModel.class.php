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
class ZdgViewModel extends ViewModel {
	/**
     +----------------------------------------------------------
    * portal分组 
     +----------------------------------------------------------
    * @access public
     +----------------------------------------------------------
    */
	public $viewFields = array(
		'zz_zdg' => array('id', 'name', 'ygbh','address','addressRegion','worktime','addressRegion'),
		'config_city' => array('nCid','sCn'=>'regionName','nParentId','_on' => 'zz_zdg.addressRegion=config_city.nCid'),
		'zz_log' => array('createUser','createTime','updateUser','updateTime','_on' => 'zz_log.tablename="zz_zdg" and zz_zdg.id=zz_log.no'),
	);
}
?>