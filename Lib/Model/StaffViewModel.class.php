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
class StaffViewModel extends ViewModel {
	/**
     +----------------------------------------------------------
    * portal分组 
     +----------------------------------------------------------
    * @access public
     +----------------------------------------------------------
    */
	public $viewFields = array(
		'zz_staff' => array('staffid', 'name', 'whcd', 'ygbh','gzjy','jg_province','jg_city','birthday','isHidden','tc','languages','address','remark','updatetime'),
		'zz_stafflevel' => array('id','level','_on' => 'zz_staff.ysLevel=zz_stafflevel.id'),
		'zz_degree' => array('degreeID','degree','_on' => 'zz_staff.whcd=zz_degree.degreeID'),
		'provincial' => array('pid','provincial','_on' => 'zz_staff.jg_province=provincial.pid'),
		'city' => array('cid','city','_on' => 'zz_staff.jg_city=city.cid'), 
	);
}
?>