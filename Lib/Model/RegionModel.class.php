<?php
// +----------------------------------------------------------------------
// | CommonCMS [IT IS LIFE]
// +----------------------------------------------------------------------
// | Copyright (c) 2013  All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: davidhuang <mchuang1140@gmail.com>
// +----------------------------------------------------------------------
// $Id$

/**
 +------------------------------------------------------------------------------
 * REGION模型類
 +------------------------------------------------------------------------------
 * @author    davidhuang <mchuang1140@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class RegionModel extends Model {
	
	/**
     +----------------------------------------------------------
     * 获取所有省份
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function getProvinces()
	{
		$result = M("config_city")->where("nParentId=0")->getField("nCid,sCn");
		if($result){
			return $result;
		}else{
			return false;
		}
	}
	
	/**
     +----------------------------------------------------------
     * 通过区县ID获取省份
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function getProvinceByRegionID($id)
	{
		if(!$id) return false;
		$id = substr($id, 0,2);
		$data['nCid'] = array("like","$id%");
		$result = M("config_city")->where($data)->find();
		if($result){
			return $result;
		}else{
			return false;
		}
	}
	
	/**
     +----------------------------------------------------------
     * 通过区县ID获取城市
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function getCityByRegionID($id)
	{
		if(!$id) return false;
		$result = M()->query("select nCid,sCn,nParentId from config_city where nCid = (select nParentId from config_city where nCid = '" . $id . "')");
		if($result){
			return $result;
		}else{
			return false;
		}
	}
	
	/**
     +----------------------------------------------------------
     * 通过ID获取名称
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function getNameByRegionID($id)
	{
		if(!$id) return false;
		$result = M("config_city")->where("nCid=" . $id)->find();
		if($result){
			return $result['sCn'];
		}else{
			return false;
		}
	}
	
	/**
     +----------------------------------------------------------
     * 通过省份ID获取城市列表
	 * 通过城市ID获取区县列表
	 * 通过父ID获取子列表
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function getSubsByParentID($id)
	{
		if(!$id) return false;
		$result = M("config_city")->where("nParentId=" . $id)->getField('nCid,sCn');
		if($result){
			return $result;
		}else{
			return false;
		}
	}
	
	/**
     +----------------------------------------------------------
     * 通过查询条件获取所有符合条件的小区.行政区域返回ID和value字段
	 * $p:分页PAGE
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function getRegionsByCond($data,$p)
	{
		$result = M("configRegion")->field("id,regionName,qxID,cityID,provinceID,address,
			updateTime,region1.sCn as province,region2.sCn as city,region3.sCn as qx")
			->join("config_city as region1 ON config_region.provinceID=region1.nCid")
			->join("config_city as region2 ON config_region.cityID=region2.nCid")
			->join("config_city as region3 ON config_region.qxID=region3.nCid")
			->where($data)->limit($p -> firstRow . " , " . $p -> listRows)->order('updateTime desc')->select();
		if($result){
			return $result;
		}else{
			return false;
		}
	}
	
	
	/**
     +----------------------------------------------------------
     * 通过查询条件获取所有符合条件的小区的数量
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function getCoutByCond($data)
	{
		$result = M("configRegion")->where($data)->count();
		return $result;
	}
	
}
	