<?php
class RegionViewModel extends ViewModel {
	/**
	 +----------------------------------------------------------
	 *
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public $viewFields = array(
		'config_region' => array('id'=>'id', 'regionName', 'cityID', 'updateTime'),
		'config_city' => array('nCid','sCn'=>'qx','nParentId', '_on' => 'config_region.cityID=config_city.nCid')
	);
}
?>