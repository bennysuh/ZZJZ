<?php
// +----------------------------------------------------------------------
// | Common CMS
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
 * 小区自定义类
 +------------------------------------------------------------------------------
 * @author    davidhuang <mchuang1104@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class RegionAction extends EntryAction
{
	public function index()
    {
    	if($_GET['regionName'])
		{
			$regionName = $_GET['regionName'];
			$data["regionName"] =  array('like',"%$regionName%");
		}
		if($_GET['provinceID'])
		{
			$data["provinceID"] =  $_GET['provinceID'];
		}
		if($_GET['cityID'])
		{
			$data["cityID"] =  $_GET['cityID'];
		}		if($_GET['qxID'])
		{
			$data["qxID"] =  $_GET['qxID'];
		}
    	$provinceList = D("Region")->getProvinces();
		import("@.ORG.Page");
		$count = D('Region')->getCoutByCond($data);
		$p = new Page($count, 10);
		$page = $p -> show();
		$list = D('Region')->getRegionsByCond($data,$p);
		$this->assign("provinceList",$provinceList);	
		$this -> assign('page', $page);	
		$this->assign("list",$list);		
		$this->display();
	}
	
	/**
     +----------------------------------------------------------
	 * 通过父ID获取子列表
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function getSubsByParentID()
	{
		if($_POST['id']) {
			$result = D("Region")->getSubsByParentID($_POST['id']);
			 if($result){
				$this->success($result);
			}else{
				$this->error("search error");
			}
		}else{
			$this->error("no id");
		}
	}
	
	public function delRegion()
	{
		$id = $_POST["id"];
		if ($id) {
			$result = M('config_region') -> where("id=" . $id) -> delete();
			SysLogs::log("删除小区,id=" . $id);
			if(is_int($result))
				$this -> success("删除成功");
			else 
				$this -> error('删除失敗');
		} else {
			$this -> error('无参数');
		}
	}
	
	public function saveRegion()
	{
		$M = M('config_region');
		$data = $M->create();
		$result = $M-> save($data);//获取新增返回的id值用于添加到联系方式表中
		if ($result) {
			SysLogs::log("修改小区,id=" . $_GET['id']);
			$this -> success("保存成功");
		} else {
			Log::write($M->getError());
			$this -> error('保存失敗');
		}
	}
	
	public function addRegion()
	{
		$M = M('config_region');
		$data = $M->create();
		$valid['regionName'] = $_POST['regionName'];
		$valid['cityID'] = $_POST['cityID'];
		$valid['qxID'] = $_POST['qxID'];
		$valid['provinceID'] = $_POST['provinceID'];
		$count = $M->where($valid)->count();
		if($count != 0){
			$this -> error('该区域已存在该小区名');
		}else{
			$key = $M ->data($data)-> add();//获取新增返回的id值用于添加到联系方式表中
			if ($key) {
				SysLogs::log("新增小区,id=" . $key);
				$this -> success("新增成功");
			} else {
				Log::write($M->getError());
				$this -> error('增加失敗');
			}
		}
	}
	
	public function editRegion()
	{
		$provinceList = D("Region")->getProvinces();
		$this->assign("provinceList",$provinceList);		
		if($_GET['id']){
			$data["id"] = $_GET['id'];
			$result = M("config_region")->where($data)->find();
			$this->assign("regionName",$result['regionName']);
			$this->assign("address",$result['address']);
			$this->assign("qxID",$result['qxID']);
			$this->assign("cityID",$result['cityID']);
			$this->assign("id",$data["id"]);
			$this->assign("provinceID",$result['provinceID']);
			$cityList = D("Region")->getSubsByParentID($result['provinceID']);
			$qxList = D("Region")->getSubsByParentID($result['cityID']);
			$this->assign("provinceList",$provinceList);		
			$this->assign("cityList",$cityList);		
			$this->assign("qxList",$qxList);		
		}
		$this->display();
	}
}