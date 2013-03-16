<?php
// +----------------------------------------------------------------------
// | PMS [ ENJOY LIFE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2012 http://pms.nmg.com.hk All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: david <davidhuang@nmg.com.hk>
// +----------------------------------------------------------------------

/**
 +------------------------------------------------------------------------------
 * 快遞控制類
 +------------------------------------------------------------------------------
 * @author    davidhuang <davidhuang@nmg.com.hk>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class CourierAction extends EntryAction
{
	/**
     +----------------------------------------------------------
     * 显示快遞公司列表
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
    public function index()
    {
		$keyword = $_GET['keyword'];
		if ($keyword) {
			$where = "name like '%$keyword%' ";
		}
		$count = M('config_courier') -> where($where) -> count();
		$list = M('config_courier') -> where($where) -> order("no desc") -> select();
		
		$this -> assign('count', $count);
		$this -> assign('list', $list);
		$this -> display();
	}
	/**
     +----------------------------------------------------------
     * 显示快遞公司列表
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function addCourier()
	{
		$courierID = $_GET["courierID"];
		if ($courierID) {
			$M = M('config_courier');
			$info = $M -> where("id = " . $courierID) -> find();
			$this -> assign("no", $info["no"]);
			$this -> assign("courier", $info["courier"]);
			$this -> assign("initial", $info["initial"]);
			$this -> assign("address", $info["address"]);
			$this -> assign("contactName", $info["contactName"]);
			$this -> assign("initial", $info["initial"]);
			$this -> assign("status", $info["status"]);
			
			//显示联系方式
			$contact = M("config_contact");
			$contactArr = $contact->where("no = " . $courierID . " and tableName = 'config_courier'")->order("id")->select();
			$this->assign("contactList",$contactArr);
			$this -> display();//不要显示2次display。
		}else{
			$this->display();
		}
	}
}
?>